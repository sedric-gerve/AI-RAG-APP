<?php

namespace App\Models;

use App\Contracts\Embeddable;
use App\Models\Concerns\HasEmbedding;
use App\Observers\EmbeddingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(EmbeddingObserver::class)]
class Contact extends Model implements Embeddable
{
    use HasEmbedding;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'notes',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function toEmbeddingText(): string
    {
        return collect([
            "Contact: {$this->full_name}",
            $this->client ? "Client: {$this->client->name}" : null,
            $this->position ? "Fonction: {$this->position}" : null,
            $this->email ? "Email: {$this->email}" : null,
            $this->phone ? "Téléphone: {$this->phone}" : null,
            $this->notes ? "Notes: {$this->notes}" : null,
        ])->filter()->implode("\n");
    }
}
