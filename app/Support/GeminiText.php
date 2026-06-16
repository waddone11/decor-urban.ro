<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client minimal Gemini (text) via REST. O singură generare per apel;
 * retry/backoff îl gestionează comanda care îl folosește (pentru manifest/resume).
 */
class GeminiText
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $model = null,
    ) {}

    public function model(): string
    {
        return $this->model ?: (string) config('services.gemini.text_model');
    }

    public function generate(string $prompt, float $temperature = 0.4, int $maxTokens = 2048): string
    {
        $key = $this->apiKey ?: (string) config('services.gemini.key');
        if ($key === '') {
            throw new RuntimeException('GEMINI_API_KEY lipsește (config/services.gemini.key).');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$this->model().':generateContent';

        $response = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $key])
            ->post($url, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                    // Task factual de rescriere — fără „thinking" (altfel consumă bugetul de tokeni
                    // pe modelele 3.x și tot textul vizibil e tăiat la MAX_TOKENS).
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini HTTP '.$response->status().': '.mb_substr($response->body(), 0, 300));
        }

        $data = $response->json();

        $finish = $data['candidates'][0]['finishReason'] ?? null;
        if ($finish === 'SAFETY' || $finish === 'BLOCKED') {
            throw new RuntimeException('Gemini a blocat răspunsul (finishReason='.$finish.').');
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini a întors răspuns gol.');
        }

        return trim($text);
    }
}
