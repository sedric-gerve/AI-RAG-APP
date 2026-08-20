<?php

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * One-off embeddings backfill for free-tier hosts with no shell/worker
 * access (see README "Déploiement"). Processes a single record per visit
 * and auto-refreshes every 22s to stay under Voyage AI's free-tier 3 RPM
 * limit, so it's safe to just open the link and leave the tab open.
 */
Route::get('/backfill-embeddings/{token}', function (string $token) {
    abort_unless(hash_equals((string) config('services.backfill_token'), $token), 404);

    $next = collect([Client::class, Contact::class, Opportunity::class, Order::class])
        ->map(fn (string $class) => $class::doesntHave('embedding')->first())
        ->filter()
        ->first();

    if (! $next) {
        return response('<p>Terminé — plus aucun enregistrement en attente d\'embedding.</p>');
    }

    GenerateEmbeddingJob::dispatchSync($next);

    $remaining = collect([Client::class, Contact::class, Opportunity::class, Order::class])
        ->sum(fn (string $class) => $class::doesntHave('embedding')->count());

    return response(
        "<p>Traité : {$next->getMorphClass()} #{$next->getKey()}. Restants : {$remaining}.</p>".
        '<p>Actualisation dans 22s (ne ferme pas cet onglet)...</p>'
    )->header('Refresh', "22; url=".url()->current());
});
