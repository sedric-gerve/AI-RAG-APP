<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case EnAttente = 'en_attente';
    case Confirmee = 'confirmee';
    case Livree = 'livree';
    case Facturee = 'facturee';
    case Annulee = 'annulee';

    public function getLabel(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Confirmee => 'Confirmée',
            self::Livree => 'Livrée',
            self::Facturee => 'Facturée',
            self::Annulee => 'Annulée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EnAttente => 'gray',
            self::Confirmee => 'info',
            self::Livree => 'warning',
            self::Facturee => 'success',
            self::Annulee => 'danger',
        };
    }
}
