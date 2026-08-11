<?php

namespace App\Contracts;

use App\Models\Embedding;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Embeddable
{
    public function embedding(): MorphOne;

    public function toEmbeddingText(): string;
}
