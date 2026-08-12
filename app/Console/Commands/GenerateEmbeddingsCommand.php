<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('embeddings:generate {--sync : Exécuter immédiatement au lieu de passer par la file d’attente}')]
#[Description('Génère (ou régénère) les embeddings de tous les enregistrements embeddable pour le RAG')]
class GenerateEmbeddingsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sync = (bool) $this->option('sync');

        $models = collect()
            ->concat(Client::all())
            ->concat(Contact::all())
            ->concat(Opportunity::all())
            ->concat(Order::all());

        $this->withProgressBar($models, function ($model) use ($sync) {
            if ($sync) {
                GenerateEmbeddingJob::dispatchSync($model);
            } else {
                GenerateEmbeddingJob::dispatch($model);
            }
        });

        $this->newLine(2);
        $this->info("{$models->count()} embeddings ".($sync ? 'générés' : 'mis en file d’attente').'.');

        return self::SUCCESS;
    }
}
