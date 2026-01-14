<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * GPT-4를 사용하여 텍스트 생성
     */
    public function generateText(string $prompt, array $options = []): ?string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $options['model'] ?? 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $options['system'] ?? 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 2000,
            ]);

            return $response->choices[0]->message->content ?? null;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * JSON 형식으로 응답받기
     */
    public function generateJson(string $prompt, array $options = []): ?array
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $options['model'] ?? 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $options['system'] ?? 'You are a helpful assistant. Always respond with valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 2000,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content ?? null;

            if ($content) {
                return json_decode($content, true);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 여러 메시지로 대화 생성
     */
    public function chat(array $messages, array $options = []): ?string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $options['model'] ?? 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 2000,
            ]);

            return $response->choices[0]->message->content ?? null;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return null;
        }
    }
}
