<?php

namespace App\Observers;

use App\Contracts\Embeddable;
use App\Jobs\GenerateEmbeddingJob;
use Illuminate\Database\Eloquent\Model;

class EmbeddingObserver
{
    /**
     * @param  Embeddable&Model  $model
     */
    public function saved(Embeddable&Model $model): void
    {
        GenerateEmbeddingJob::dispatch($model);
    }

    /**
     * @param  Embeddable&Model  $model
     */
    public function deleted(Embeddable&Model $model): void
    {
        $model->embedding()->delete();
    }
}
