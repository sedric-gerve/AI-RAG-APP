<?php

namespace App\Jobs;

use App\Contracts\Embeddable;
use App\Services\VoyageEmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $vector = $voyage->embed($text, inputType: 'document');

        $this->embeddable->embedding()->updateOrCreate([], [
            'content' => $text,
            'vector' => $vector,
            'model' => $voyage->modelName(),
        ]);
    }
}
