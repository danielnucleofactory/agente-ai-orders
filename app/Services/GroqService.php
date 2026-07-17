<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private array $apiKeys;
    private string $model;
    private string $apiUrl;
    private int $maxToolIterations = 6;

    public function __construct()
    {
        // Cascada de API keys — cuando una se agota, pasa a la siguiente
        // NOTA: Para producción solo usar GROQ_API_KEY principal
        // Las keys 2-5 son para desarrollo/pruebas intensivas únicamente
        $this->apiKeys = array_values(array_filter([
            config('services.groq.api_key'),
            // config('services.groq.api_key_2'), // DEV ONLY — desactivado para producción
            // config('services.groq.api_key_3'), // DEV ONLY — desactivado para producción
            // config('services.groq.api_key_4'), // DEV ONLY — desactivado para producción
            // config('services.groq.api_key_5'), // DEV ONLY — desactivado para producción
        ]));

        $this->model  = config('agent.models.primary.model', 'llama-3.3-70b-versatile');
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function chat(array $messages, array $tools = []): string
    {
        foreach ($this->apiKeys as $index => $apiKey) {
            $keyNumber = $index + 1;
            $result = $this->tryWithKey($apiKey, $keyNumber, $messages, $tools);

            if ($result !== null) {
                return $result;
            }
        }

        // Todas las keys de Groq agotadas
        // Fallback desactivado para producción — reactivar si se aprueba
        // return app(GeminiService::class)->chat($messages, $tools);
        Log::warning('Todas las keys de Groq agotadas — fallback desactivado');
        return 'El servicio de IA no está disponible temporalmente. Por favor intenta en unos minutos.';
    }

    private function tryWithKey(string $apiKey, int $keyNumber, array $messages, array $tools): ?string
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
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            if ($response->status() === 429) {
                Log::warning("Groq key #{$keyNumber} agotada, probando siguiente key");
                return null;
            }

            if ($response->failed()) {
                Log::error("Groq API error con key #{$keyNumber}", [
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
                return $this->runToolLoop($message, $messages, $tools, $apiKey, $keyNumber);
            }

            return $this->cleanResponse($message['content'] ?? 'Sin respuesta.');

        } catch (\Exception $e) {
            Log::error("Groq Service exception con key #{$keyNumber}", ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    private function runToolLoop(array $firstMessage, array $messages, array $tools, string $currentKey, int $keyNumber): string
    {
        $currentMessage = $firstMessage;
        $iteration      = 0;

        while ($iteration < $this->maxToolIterations) {
            $iteration++;

            if (!isset($currentMessage['tool_calls']) || empty($currentMessage['tool_calls'])) {
                $content = $currentMessage['content'] ?? null;
                if (!empty($content)) {
                    return $this->cleanResponse($content);
                }
                break;
            }

            $toolCalls = $currentMessage['tool_calls'];

            $messages[] = [
                'role'       => 'assistant',
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $toolCall) {
                $toolName = $toolCall['function']['name'];
                $args     = json_decode($toolCall['function']['arguments'], true) ?? [];
                $result   = $this->executeTool($toolName, $args);

                Log::info('[Groq] Tool ejecutada', [
                    'tool'      => $toolName,
                    'key'       => $keyNumber,
                    'iteration' => $iteration,
                    'result'    => array_keys($result),
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
                'tools'       => $tools,
                'tool_choice' => 'auto',
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $currentKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post($this->apiUrl, $payload);

                if ($response->status() === 429) {
                    Log::warning("Groq key #{$keyNumber} agotada en bucle, buscando siguiente key");
                    $nextKey = $this->getNextAvailableKey($keyNumber);

                    if ($nextKey !== null) {
                        $currentKey = $nextKey['key'];
                        $keyNumber  = $nextKey['number'];
                        Log::info("Groq continuando bucle con key #{$keyNumber}");
                        continue;
                    }

                    // No hay más keys
                    Log::warning('Todas las keys de Groq agotadas en bucle — fallback desactivado');
                    return 'El servicio de IA no está disponible temporalmente. Por favor intenta en unos minutos.';
                }

                if ($response->failed()) {
                    Log::error('Groq API error en bucle tool calls', [
                        'status'    => $response->status(),
                        'key'       => $keyNumber,
                        'iteration' => $iteration,
                    ]);
                    return config('agent.error_messages.query_error',
                        'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.'
                    );
                }

                $data           = $response->json();
                $currentMessage = $data['choices'][0]['message'] ?? null;

                if (!$currentMessage) {
                    break;
                }

                if (empty($currentMessage['tool_calls']) && !empty($currentMessage['content'])) {
                    Log::info('[Groq] Respuesta final obtenida', [
                        'iteration' => $iteration,
                        'key'       => $keyNumber,
                    ]);
                    return $this->cleanResponse($currentMessage['content']);
                }

            } catch (\Exception $e) {
                Log::error('Groq runToolLoop exception', [
                    'iteration' => $iteration,
                    'key'       => $keyNumber,
                    'error'     => $e->getMessage(),
                ]);
                return config('agent.error_messages.query_error',
                    'Ocurrió un error de conexión. Por favor intenta de nuevo.'
                );
            }
        }

        Log::warning('Groq runToolLoop: máximo de iteraciones alcanzado');
        return 'Lo siento, no pude completar la consulta. Por favor intenta de nuevo.';
    }

    private function getNextAvailableKey(int $currentKeyNumber): ?array
    {
        $keys = array_values($this->apiKeys);
        $currentIndex = $currentKeyNumber - 1;

        for ($i = $currentIndex + 1; $i < count($keys); $i++) {
            if (!empty($keys[$i])) {
                return [
                    'key'    => $keys[$i],
                    'number' => $i + 1,
                ];
            }
        }

        return null;
    }

    private function cleanResponse(string $content): string
    {
        $maxIterations = 5;
        for ($i = 0; $i < $maxIterations; $i++) {
            $cleaned = preg_replace('/\{[^{}]*\}/', '', $content);
            if ($cleaned === $content) break;
            $content = $cleaned;
        }

        $lines      = explode("\n", $content);
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