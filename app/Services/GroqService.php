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

            // Rate limit — Gemini fallback desactivado temporalmente
            // Para reactivar: reemplazar el return por:
            // return app(GeminiService::class)->chat($messages, $tools);
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

            // Soporte para múltiples tool calls
            if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
                return $this->handleMultipleToolCalls($message['tool_calls'], $messages, $tools);
            }

            return $message['content'] ?? 'Sin respuesta.';

        } catch (\Exception $e) {
            Log::error('Groq Service exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    /**
     * Maneja una o múltiples tool calls de Groq en una sola respuesta.
     */
    private function handleMultipleToolCalls(array $toolCalls, array $messages, array $tools): string
    {
        // Agregar mensaje del asistente con todas las tool calls
        $messages[] = [
            'role'       => 'assistant',
            'tool_calls' => $toolCalls,
        ];

        // Ejecutar todas las tools y agregar sus resultados
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

        // Segunda llamada con todos los resultados
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

            // Rate limit en segunda llamada — Gemini fallback desactivado temporalmente
            // Para reactivar: reemplazar el return por:
            // return app(GeminiService::class)->chat($messages, $tools);
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

            return $content;

        } catch (\Exception $e) {
            Log::error('Groq handleMultipleToolCalls exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    private function executeTool(string $toolName, array $args): array
    {
        return app(RagaOrdersToolService::class)->execute($toolName, $args);
    }
}