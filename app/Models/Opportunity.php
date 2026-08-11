<?php

namespace App\Models;

use App\Contracts\Embeddable;
use App\Enums\OpportunityStage;
use App\Models\Concerns\HasEmbedding;
use App\Observers\EmbeddingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(EmbeddingObserver::class)]
class Opportunity extends Model implements Embeddable
{
    use HasEmbedding;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'amount',
        'stage',
        'probability',
        'expected_close_date',
        'notes',
    ];

    protected $casts = [
        'stage' => OpportunityStage::class,
        'amount' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function toEmbeddingText(): string
    {
        return collect([
            "Opportunité: {$this->title}",
            "Client: {$this->client?->name}",
            'Étape: '.$this->stage->getLabel(),
            $this->amount ? 'Montant estimé: '.number_format((float) $this->amount, 2).' €' : null,
            "Probabilité: {$this->probability}%",
            $this->expected_close_date ? 'Date de clôture prévue: '.$this->expected_close_date->format('d/m/Y') : null,
            $this->notes ? "Notes: {$this->notes}" : null,
        ])->filter()->implode("\n");
    }
}
