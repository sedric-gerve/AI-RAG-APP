<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\OpportunityStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OpportunitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'opportunities';

    protected static ?string $title = 'Opportunités';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('stage')
                    ->label('Étape')
                    ->options(OpportunityStage::class)
                    ->required()
                    ->default(OpportunityStage::Prospection),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant estimé')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('probability')
                    ->label('Probabilité (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Forms\Components\DatePicker::make('expected_close_date')
                    ->label('Date de clôture prévue'),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Étape')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('probability')
                    ->label('Probabilité')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->label('Clôture prévue')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
