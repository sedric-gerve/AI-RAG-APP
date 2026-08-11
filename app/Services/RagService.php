<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\Embedding;
use Illuminate\Support\Collection;
use RuntimeException;

class RagService
{
    protected Client $client;

    public function __construct(protected VoyageEmbeddingService $embeddings)
    {
        $apiKey = (string) config('services.anthropic.key');

        if ($apiKey === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY manquante. Ajoute-la dans le fichier .env.');
        }

        $this->client = new Client(apiKey: $apiKey);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history  Tours précédents de la conversation (le plus ancien en premier)
     */
    public function ask(string $question, array $history = [], int $topK = 8, int $maxHistoryMessages = 10): string
    {
        $searchQuery = $this->buildSearchQuery($history, $question);
        $questionVector = $this->embeddings->embed($searchQuery, inputType: 'query');

        $context = $this->findRelevant($questionVector, $topK);

        if ($context->isEmpty()) {
            return "Je n'ai trouvé aucune donnée pertinente dans le CRM pour répondre à cette question. As-tu déjà généré les embeddings des clients et contacts existants ?";
        }

        $contextText = $context
            ->map(fn (Embedding $embedding, int $i) => "[Enregistrement ".($i + 1)."]\n{$embedding->content}")
            ->implode("\n\n");

        $recentHistory = collect($history)->slice(-$maxHistoryMessages)->values();

        $messages = $recentHistory
            ->map(fn (array $turn) => ['role' => $turn['role'], 'content' => $turn['content']])
            ->push([
                'role' => 'user',
                'content' => "Contexte (extrait du CRM, susceptible de couvrir une nouvelle personne) :\n\n{$contextText}\n\nQuestion : {$question}",
            ])
            ->all();

        $message = $this->client->messages->create(
            model: (string) config('services.anthropic.model'),
            maxTokens: 1024,
            system: [
                [
                    'type' => 'text',
                    'text' => <<<'PROMPT'
                        Tu es l'assistant IA d'un CRM interne. Réponds aux questions de l'utilisateur
                        UNIQUEMENT à partir des enregistrements du contexte fourni (clients et contacts)
                        et de l'historique de la conversation. Si l'information n'est pas disponible,
                        dis clairement que tu ne sais pas — n'invente jamais de données.
                        Réponds en français, de façon concise.
                        PROMPT,
                ],
            ],
            messages: $messages,
        );

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return $block->text;
            }
        }

        return "Je n'ai pas pu générer de réponse.";
    }

    /**
     * Combine les derniers échanges avec la nouvelle question pour que la recherche
     * vectorielle tienne compte du contexte (ex : "quel est SON âge" après avoir parlé d'un client).
     *
     * @param  array<int, array{role: string, content: string}>  $history
     */
    protected function buildSearchQuery(array $history, string $question, int $recentTurns = 2): string
    {
        $recent = collect($history)
            ->slice(-$recentTurns * 2)
            ->map(fn (array $turn) => $turn['content'])
            ->implode("\n");

        return trim($recent."\n".$question);
    }

    /**
     * @param  array<int, float>  $questionVector
     * @return Collection<int, Embedding>
     */
    protected function findRelevant(array $questionVector, int $topK): Collection
    {
        return Embedding::with('embeddable')
            ->get()
            ->map(function (Embedding $embedding) use ($questionVector) {
                $embedding->similarity = $embedding->cosineSimilarity($questionVector);

                return $embedding;
            })
            ->filter(fn (Embedding $embedding) => $embedding->embeddable !== null)
            ->sortByDesc('similarity')
            ->take($topK);
    }
}
