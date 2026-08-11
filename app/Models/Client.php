<?php

namespace App\Models;

use App\Contracts\Embeddable;
use App\Models\Concerns\HasEmbedding;
use App\Observers\EmbeddingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(EmbeddingObserver::class)]
class Client extends Model implements Embeddable
{
    use HasEmbedding;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
        'notes',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function toEmbeddingText(): string
    {
        $contacts = $this->contacts
            ->map(fn (Contact $contact) => trim("{$contact->full_name} ({$contact->position})"))
            ->implode(', ');

        $opportunities = $this->opportunities
            ->map(fn (Opportunity $opportunity) => "{$opportunity->title} ({$opportunity->stage->getLabel()})")
            ->implode(', ');

        $orders = $this->orders
            ->map(fn (Order $order) => "{$order->reference} ({$order->status->getLabel()}, ".number_format((float) $order->total, 2).' €)')
            ->implode(', ');

        return collect([
            "Client: {$this->name}",
            $this->email ? "Email: {$this->email}" : null,
            $this->phone ? "Téléphone: {$this->phone}" : null,
            $this->website ? "Site web: {$this->website}" : null,
            collect([$this->address, $this->city, $this->postal_code, $this->country])
                ->filter()
                ->isNotEmpty()
                ? 'Adresse: '.collect([$this->address, $this->city, $this->postal_code, $this->country])->filter()->implode(', ')
                : null,
            $contacts !== '' ? "Contacts: {$contacts}" : null,
            $opportunities !== '' ? "Opportunités: {$opportunities}" : null,
            $orders !== '' ? "Commandes: {$orders}" : null,
            $this->notes ? "Notes: {$this->notes}" : null,
        ])->filter()->implode("\n");
    }
}
