<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OpportunityStage: string implements HasColor, HasLabel
{
    case Prospection = 'prospection';
    case Qualification = 'qualification';
    case Proposition = 'proposition';
    case Negociation = 'negociation';
    case Gagnee = 'gagnee';
    case Perdue = 'perdue';

    public function getLabel(): string
    {
        return match ($this) {
            self::Prospection => 'Prospection',
            self::Qualification => 'Qualification',
            self::Proposition => 'Proposition',
            self::Negociation => 'Négociation',
            self::Gagnee => 'Gagnée',
            self::Perdue => 'Perdue',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Prospection => 'gray',
            self::Qualification => 'info',
            self::Proposition => 'warning',
            self::Negociation => 'warning',
            self::Gagnee => 'success',
            self::Perdue => 'danger',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Gagnee, self::Perdue], true);
    }
}
