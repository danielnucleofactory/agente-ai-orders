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
        $this->model  = 'llama-3.3-70b-versatile';
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function chat(array $messages, array $tools = []): string
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.3,
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

            if ($response->failed()) {
                Log::error('Groq API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.';
            }

            $data    = $response->json();
            $message = $data['choices'][0]['message'] ?? null;

            if (!$message) {
                return 'No pude obtener una respuesta. Por favor intenta de nuevo.';
            }

            if (isset($message['tool_calls'])) {
                return $this->handleToolCalls($message['tool_calls'], $messages, $tools);
            }

            return $message['content'] ?? 'Sin respuesta.';

        } catch (\Exception $e) {
            Log::error('Groq Service exception', ['error' => $e->getMessage()]);
            return 'Ocurrió un error de conexión. Por favor intenta de nuevo.';
        }
    }

    private function handleToolCalls(array $toolCalls, array $messages, array $tools): string
    {
        $messages[] = [
            'role'       => 'assistant',
            'tool_calls' => $toolCalls,
        ];

        foreach ($toolCalls as $toolCall) {
            $toolName = $toolCall['function']['name'];
            $args     = json_decode($toolCall['function']['arguments'], true) ?? [];
            $result   = $this->executeTool($toolName, $args);

            $messages[] = [
                'role'         => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content'      => json_encode($result),
            ];
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.3,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->apiUrl, $payload);

            if ($response->failed()) {
                Log::error('Groq API error (segunda llamada)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 'Lo siento, hubo un error al procesar tu consulta. Por favor intenta de nuevo.';
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (empty($content)) {
                Log::warning('Groq segunda llamada sin contenido', ['data' => $data]);
                return 'Lo siento, no pude generar una respuesta. Por favor intenta de nuevo.';
            }

            return $content;

        } catch (\Exception $e) {
            Log::error('Groq handleToolCalls exception', ['error' => $e->getMessage()]);
            return 'Ocurrió un error de conexión. Por favor intenta de nuevo.';
        }
    }

    private function executeTool(string $toolName, array $args): array
    {
        return app(RagaOrdersToolService::class)->execute($toolName, $args);
    }
}