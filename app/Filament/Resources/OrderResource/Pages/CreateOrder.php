<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Jobs\GenerateEmbeddingJob;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        // Les lignes de commande (repeater) sont enregistrées après la commande elle-même ;
        // on régénère donc l'embedding une fois le total final connu.
        GenerateEmbeddingJob::dispatch($this->record);
    }
}
