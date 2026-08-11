<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Embedding extends Model
{
    protected $fillable = [
        'embeddable_type',
        'embeddable_id',
        'content',
        'vector',
        'model',
    ];

    protected $casts = [
        'vector' => 'array',
    ];

    public function embeddable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cosineSimilarity(array $other): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($this->vector as $i => $value) {
            $dotProduct += $value * $other[$i];
            $normA += $value ** 2;
            $normB += $other[$i] ** 2;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
