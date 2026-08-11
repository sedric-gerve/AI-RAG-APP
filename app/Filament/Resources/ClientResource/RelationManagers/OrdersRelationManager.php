<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Jobs\GenerateEmbeddingJob;
use App\Models\Opportunity;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Commandes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('opportunity_id')
                    ->label('Opportunité liée (optionnel)')
                    ->options(fn () => Opportunity::query()
                        ->where('client_id', $this->getOwnerRecord()->id)
                        ->pluck('title', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options(OrderStatus::class)
                    ->required()
                    ->default(OrderStatus::EnAttente),
                Forms\Components\DatePicker::make('ordered_at')
                    ->label('Date de commande')
                    ->required()
                    ->default(now()),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->label('Articles')
                    ->addActionLabel('Ajouter un article')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Produit')
                            ->options(fn () => Product::query()->where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $product = Product::find($state);

                                if ($product) {
                                    $set('description', $product->name);
                                    $set('unit_price', (float) $product->unit_price);
                                }
                            })
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Prix unitaire')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->defaultItems(1)
                    ->required()
                    ->minItems(1)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('ordered_at')
                    ->label('Date')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn ($record) => GenerateEmbeddingJob::dispatch($record)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn ($record) => GenerateEmbeddingJob::dispatch($record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
