<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class VoyageEmbeddingService
{
    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.voyageai.key');
        $this->model = (string) config('services.voyageai.model');
    }

    /**
     * @return array<int, float>
     */
    public function embed(string $text, string $inputType = 'document'): array
    {
        return $this->embedBatch([$text], $inputType)[0];
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     */
    public function embedBatch(array $texts, string $inputType = 'document'): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('VOYAGE_API_KEY manquante. Ajoute-la dans le fichier .env.');
        }

        $response = Http::withToken($this->apiKey)
            ->baseUrl('https://api.voyageai.com/v1')
            ->post('/embeddings', [
                'input' => $texts,
                'model' => $this->model,
                'input_type' => $inputType,
            ])
            ->throw();

        return collect($response->json('data'))
            ->sortBy('index')
            ->pluck('embedding')
            ->values()
            ->all();
    }

    public function modelName(): string
    {
        return $this->model;
    }
}
