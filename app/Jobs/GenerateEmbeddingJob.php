<?php

namespace App\Jobs;

use App\Contracts\Embeddable;
use App\Services\VoyageEmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Embeddable&\Illuminate\Database\Eloquent\Model  $embeddable
     */
    public function __construct(public Embeddable $embeddable)
    {
        //
    }

    public function handle(VoyageEmbeddingService $voyage): void
    {
        $text = $this->embeddable->toEmbeddingText();

        // Best-effort: on QUEUE_CONNECTION=sync (used for the free-tier
        // deployment, see README), a Voyage failure here would otherwise
        // propagate synchronously and abort whatever triggered the save
        // (including `php artisan db:seed` at boot). A record without an
        // embedding just stays outside RAG search results until the next
        // successful save or backfill — not ideal, but never fatal.
        try {
            $vector = $voyage->embed($text, inputType: 'document');

            $this->embeddable->embedding()->updateOrCreate([], [
                'content' => $text,
                'vector' => $vector,
                'model' => $voyage->modelName(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Embedding generation failed, skipping.', [
                'embeddable_type' => $this->embeddable::class,
                'embeddable_id' => $this->embeddable->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
