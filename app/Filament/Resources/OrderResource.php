<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'commande';

    protected static ?string $pluralModelLabel = 'commandes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Commande')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->options(fn () => Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('opportunity_id')
                            ->label('Opportunité liée (optionnel)')
                            ->options(
                                fn (Get $get) => Opportunity::query()
                                    ->where('client_id', $get('client_id'))
                                    ->pluck('title', 'id')
                            )
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
                    ]),
                Forms\Components\Section::make('Articles')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->label('')
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
                            ->minItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ordered_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(OrderStatus::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('ordered_at', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
