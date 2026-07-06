<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model  = config('agent.models.primary.model', 'llama-3.3-70b-versatile');
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function chat(array $messages, array $tools = []): string
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => config('agent.generation.max_tokens', 1024),
            'temperature' => config('agent.generation.temperature', 0.3),
        ];

        if (!empty($tools)) {
            $payload['tools']       = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            // Rate limit — fallback desactivado, pendiente aprobación
            // Para reactivar: return app(GeminiService::class)->chat($messages, $tools);
            if ($response->status() === 429) {
                Log::warning('Groq rate limit alcanzado — fallback desactivado temporalmente');
                return config('agent.error_messages.rate_limit_gemini',
                    'Estoy experimentando alta demanda en este momento. Por favor espera un momento e intenta de nuevo.'
                );
            }

            if ($response->failed()) {
                Log::error('Groq API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return config('agent.error_messages.query_error',
                    'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.'
                );
            }

            $data    = $response->json();
            $message = $data['choices'][0]['message'] ?? null;

            if (!$message) {
                return 'No pude obtener una respuesta. Por favor intenta de nuevo.';
            }

            if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
                return $this->handleMultipleToolCalls($message['tool_calls'], $messages, $tools);
            }

            return $this->cleanResponse($message['content'] ?? 'Sin respuesta.');

        } catch (\Exception $e) {
            Log::error('Groq Service exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    private function handleMultipleToolCalls(array $toolCalls, array $messages, array $tools): string
    {
        $messages[] = [
            'role'       => 'assistant',
            'tool_calls' => $toolCalls,
        ];

        foreach ($toolCalls as $toolCall) {
            $toolName = $toolCall['function']['name'];
            $args     = json_decode($toolCall['function']['arguments'], true) ?? [];
            $result   = $this->executeTool($toolName, $args);

            Log::info('[Groq] Tool ejecutada', [
                'tool'   => $toolName,
                'result' => array_keys($result),
            ]);

            $messages[] = [
                'role'         => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content'      => json_encode($result),
            ];
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => config('agent.generation.max_tokens', 1024),
            'temperature' => config('agent.generation.temperature', 0.3),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            // Rate limit en segunda llamada — fallback desactivado, pendiente aprobación
            // Para reactivar: return app(GeminiService::class)->chat($messages, $tools);
            if ($response->status() === 429) {
                Log::warning('Groq rate limit en segunda llamada — fallback desactivado temporalmente');
                return config('agent.error_messages.rate_limit_gemini',
                    'Estoy experimentando alta demanda en este momento. Por favor espera un momento e intenta de nuevo.'
                );
            }

            if ($response->failed()) {
                Log::error('Groq API error (segunda llamada)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return config('agent.error_messages.query_error',
                    'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.'
                );
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (empty($content)) {
                Log::warning('Groq segunda llamada sin contenido', ['data' => $data]);
                return 'Lo siento, no pude generar una respuesta. Por favor intenta de nuevo.';
            }

            return $this->cleanResponse($content);

        } catch (\Exception $e) {
            Log::error('Groq handleMultipleToolCalls exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    /**
     * Limpia la respuesta eliminando artefactos técnicos y JSON crudo.
     */
    private function cleanResponse(string $content): string
    {
        // 1. Eliminar JSON anidado iterativamente
        $maxIterations = 5;
        for ($i = 0; $i < $maxIterations; $i++) {
            $cleaned = preg_replace('/\{[^{}]*\}/', '', $content);
            if ($cleaned === $content) break;
            $content = $cleaned;
        }

        // 2. Limpiar línea por línea
        $lines = explode("\n", $content);
        $cleanLines = [];

        $debugPhrases = [
            'We need to call',
            'We need to simulate',
            'We need to actually call',
            'We need to get result',
            'We need to wait',
            'We need TEUs',
            'I need to call',
            'Let me call',
            'Calling tool',
            'Tool call:',
            'tool_call',
            'get_teus_summary',
            'get_orders',
            'query_operational',
            'get_full_summary',
        ];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                $cleanLines[] = '';
                continue;
            }

            $isDebug = false;
            foreach ($debugPhrases as $phrase) {
                if (stripos($trimmed, $phrase) !== false) {
                    $isDebug = true;
                    break;
                }
            }

            if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                $isDebug = true;
            }

            if (!$isDebug) {
                $cleanLines[] = $line;
            }
        }

        $content = implode("\n", $cleanLines);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        if (empty($content) || strlen($content) < 10) {
            return 'Lo siento, no pude generar una respuesta. Por favor intenta de nuevo.';
        }

        return $content;
    }

    private function executeTool(string $toolName, array $args): array
    {
        return app(RagaOrdersToolService::class)->execute($toolName, $args);
    }
}