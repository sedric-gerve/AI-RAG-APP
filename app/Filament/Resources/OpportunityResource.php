<?php

namespace App\Filament\Resources;

use App\Enums\OpportunityStage;
use App\Filament\Resources\OpportunityResource\Pages;
use App\Models\Client;
use App\Models\Opportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'opportunité';

    protected static ?string $pluralModelLabel = 'opportunités';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Opportunité')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->options(fn () => Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Étape')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('probability')
                    ->label('Probabilité')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->label('Clôture prévue')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Étape')
                    ->options(OpportunityStage::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpportunities::route('/'),
            'create' => Pages\CreateOpportunity::route('/create'),
            'edit' => Pages\EditOpportunity::route('/{record}/edit'),
        ];
    }
}
