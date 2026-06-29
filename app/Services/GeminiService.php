<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model  = config('agent.models.fallback.model', 'gemini-2.5-flash');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . $this->model
            . ':generateContent?key='
            . config('services.gemini.api_key');
    }

    public function chat(array $messages, array $tools = []): string
    {
        $systemPrompt = '';
        $chatMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemPrompt = $message['content'];
            } else {
                $chatMessages[] = $message;
            }
        }

        $contents = [];
        foreach ($chatMessages as $message) {
            if (!isset($message['content']) || !is_string($message['content'])) {
                continue;
            }
            $contents[] = [
                'role'  => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ];
        }

        $payload = [
            'contents'          => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig'  => [
                'temperature'     => config('agent.generation.temperature', 0.3),
                'maxOutputTokens' => config('agent.generation.max_tokens', 1024),
            ],
        ];

        if (!empty($tools)) {
            $functionDeclarations = [];
            foreach ($tools as $tool) {
                $functionDeclarations[] = [
                    'name'        => $tool['function']['name'],
                    'description' => $tool['function']['description'],
                    'parameters'  => $tool['function']['parameters'],
                ];
            }
            $payload['tools'] = [
                ['functionDeclarations' => $functionDeclarations],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->status() === 429 || $response->status() === 503) {
                    Log::warning('Gemini rate limit alcanzado — Cerebras fallback desactivado temporalmente');
                    // Para reactivar cuando el jefe apruebe modelos adicionales:
                    // return app(CerebrasService::class)->chat($messages, $tools);
                    return config('agent.error_messages.both_unavailable',
                        'El servicio de IA no está disponible temporalmente. Por favor intenta en unos minutos.'
                    );
                }

                return config('agent.error_messages.query_error',
                    'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.'
                );
            }

            $data      = $response->json();
            $candidate = $data['candidates'][0] ?? null;

            if (!$candidate) {
                Log::warning('Gemini sin candidato — Cerebras fallback desactivado temporalmente');
                // Para reactivar: return app(CerebrasService::class)->chat($messages, $tools);
                return 'No pude obtener una respuesta. Por favor intenta de nuevo.';
            }

            $parts = $candidate['content']['parts'] ?? [];

            $toolCallParts = array_filter($parts, fn($p) => isset($p['functionCall']));

            if (!empty($toolCallParts)) {
                return $this->handleMultipleToolCalls(
                    array_values($toolCallParts),
                    $contents,
                    $tools,
                    $systemPrompt,
                    $messages
                );
            }

            foreach ($parts as $part) {
                if (isset($part['text']) && !empty(trim($part['text']))) {
                    return $part['text'];
                }
            }

            Log::warning('Gemini respuesta vacía — Cerebras fallback desactivado temporalmente');
            // Para reactivar: return app(CerebrasService::class)->chat($messages, $tools);
            return 'Lo siento, no pude generar una respuesta. Por favor intenta de nuevo.';

        } catch (\Exception $e) {
            Log::error('Gemini Service exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }

    private function handleMultipleToolCalls(
        array $toolCallParts,
        array $contents,
        array $tools,
        string $systemPrompt,
        array $originalMessages = []
    ): string {
        $modelParts = [];
        foreach ($toolCallParts as $part) {
            $modelParts[] = [
                'functionCall' => [
                    'name' => $part['functionCall']['name'],
                    'args' => (object)($part['functionCall']['args'] ?? []),
                ],
            ];
        }

        $contents[] = [
            'role'  => 'model',
            'parts' => $modelParts,
        ];

        $responseParts = [];
        foreach ($toolCallParts as $part) {
            $toolName = $part['functionCall']['name'];
            $args     = $part['functionCall']['args'] ?? [];
            $result   = app(RagaOrdersToolService::class)->execute($toolName, $args);

            Log::info('[Gemini] Tool ejecutada', [
                'tool'   => $toolName,
                'result' => array_keys($result),
            ]);

            $responseParts[] = [
                'functionResponse' => [
                    'name'     => $toolName,
                    'response' => ['result' => json_encode($result)],
                ],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => $responseParts,
        ];

        $payload = [
            'contents'          => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig'  => [
                'temperature'     => config('agent.generation.temperature', 0.3),
                'maxOutputTokens' => config('agent.generation.max_tokens', 1024),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            if ($response->failed()) {
                Log::error('Gemini API error (tool response)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->status() === 429 || $response->status() === 503) {
                    Log::warning('Gemini rate limit en segunda llamada — Cerebras fallback desactivado temporalmente');
                    // Para reactivar: return app(CerebrasService::class)->chat($originalMessages, $tools);
                    return config('agent.error_messages.both_unavailable',
                        'El servicio de IA no está disponible temporalmente. Por favor intenta en unos minutos.'
                    );
                }

                return config('agent.error_messages.query_error',
                    'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.'
                );
            }

            $data  = $response->json();
            $parts = $data['candidates'][0]['content']['parts'] ?? [];

            foreach ($parts as $part) {
                if (isset($part['text']) && !empty(trim($part['text']))) {
                    return $part['text'];
                }
            }

            Log::warning('Gemini respuesta vacía en tool call — Cerebras fallback desactivado temporalmente');
            // Para reactivar: return app(CerebrasService::class)->chat($originalMessages, $tools);
            return 'Lo siento, no pude generar una respuesta. Por favor intenta de nuevo.';

        } catch (\Exception $e) {
            Log::error('Gemini handleMultipleToolCalls exception', ['error' => $e->getMessage()]);
            return config('agent.error_messages.query_error',
                'Ocurrió un error de conexión. Por favor intenta de nuevo.'
            );
        }
    }
}