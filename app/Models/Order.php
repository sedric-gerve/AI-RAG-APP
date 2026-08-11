<?php

namespace App\Models;

use App\Contracts\Embeddable;
use App\Enums\OrderStatus;
use App\Models\Concerns\HasEmbedding;
use App\Observers\EmbeddingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(EmbeddingObserver::class)]
class Order extends Model implements Embeddable
{
    use HasEmbedding;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'opportunity_id',
        'reference',
        'status',
        'total',
        'ordered_at',
        'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total' => 'decimal:2',
        'ordered_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->reference ??= static::generateReference();
        });
    }

    public static function generateReference(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', now()->year)->count() + 1;

        return sprintf('CMD-%s-%04d', $year, $count);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal(): void
    {
        // updateQuietly : le total est une donnée dérivée des lignes de commande.
        // Le recalculer ne doit pas redéclencher un job d'embedding à chaque ligne
        // ajoutée/modifiée (sinon une commande de N lignes = N appels API en rafale).
        $this->fill(['total' => $this->items()->sum('subtotal')])->saveQuietly();
    }

    public function toEmbeddingText(): string
    {
        $items = $this->items
            ->map(fn (OrderItem $item) => "{$item->quantity} x {$item->description} (".number_format((float) $item->subtotal, 2).' €)')
            ->implode(', ');

        return collect([
            "Commande: {$this->reference}",
            "Client: {$this->client?->name}",
            'Statut: '.$this->status->getLabel(),
            'Total: '.number_format((float) $this->total, 2).' €',
            'Date de commande: '.$this->ordered_at?->format('d/m/Y'),
            $items !== '' ? "Articles: {$items}" : null,
            $this->notes ? "Notes: {$this->notes}" : null,
        ])->filter()->implode("\n");
    }
}
