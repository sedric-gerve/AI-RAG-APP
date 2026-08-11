<?php

namespace App\Models\Concerns;

use App\Models\Embedding;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEmbedding
{
    public function embedding(): MorphOne
    {
        return $this->morphOne(Embedding::class, 'embeddable');
    }

    abstract public function toEmbeddingText(): string;
}
