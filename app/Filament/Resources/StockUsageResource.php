<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\StockUsage;
use Filament\Infolists;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeStationeryItem;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockUsageResource\Pages;
use App\Filament\Resources\StockUsageResource\RelationManagers;

class StockUsageResource extends Resource
{
    protected static ?string $model = StockUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationParentItem = 'Stocks';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('division_id')
                ->default(fn() => auth()->user()->division_id),
                Forms\Components\Hidden::make('requester_id')
                ->default(fn() => auth()->user()->id),
                Forms\Components\Hidden::make('requested_at')
                ->default(fn() => now()),
                Forms\Components\Repeater::make('items')
                    ->columnSpanFull()
                    ->relationship('items')
                    ->schema([
                            Forms\Components\Select::make('item_id')
                                ->label('Item')
                                ->relationship('item', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        Forms\Components\Hidden::make('category_id')
                            ->default(function (Forms\Get $get) {
                                $itemId = $get('item_id');
                                if ($itemId) {
                                    $item = OfficeStationeryItem::find($itemId);
                                    return $item?->category->id;
                                }
                                return null;
                            }),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1),
                            Forms\Components\Textarea::make('notes')
                                ->maxLength(1000)
                                ->columnSpanFull(),
                        ])->columns(2)
                    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('division_id', auth()->user()->division_id)->orderByDesc('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('requester.name'),
                Tables\Columns\TextColumn::make('requested_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items.item.name') // relation: StockUsage → StockUsageItem → OfficeStationeryItem
                    ->label('Items')
                    ->formatStateUsing(function ($record) {
                        return $record->items
                            ->map(fn($item) => $item->item->name . ' (' . $item->quantity .' '. $item->item->unit_of_measure . ')')
                            ->join(', ');
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(function($record) {
                        return match ($record->status) {
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        };
                    }),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending' && auth()->user()->division_id === $record->division_id && auth()->user()->hasRole('Head'))
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Update status
                            $record->update([
                                'status' => 'approved',
                                'head_id' => auth()->user()->id,
                                'approved_at' => now()
                            ]);

                            // Decrement DivisionStock
                            foreach ($record->items as $item) {
                                $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                    ->where('item_id', $item->item_id)
                                    ->first();

                                if ($officeStationeryStockPerDivision) {
                                    $officeStationeryStockPerDivision->decrement('current_stock', $item->quantity);
                                }
                            }
                        });
                    }),
                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->form([
                            Forms\Components\Textarea::make('reject_reason')
                                ->label('Reason for Rejection')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->modalHeading('Reject Stock Usage')
                        ->modalButton('Reject')
                        ->requiresConfirmation() // optional
                        ->visible(fn($record) => $record->status === 'pending' && auth()->user()->division_id === $record->division_id && auth()->user()->hasRole('Head'))
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'head_id' => auth()->user()->id,
                                'rejected_at' => now(),
                                'reject_reason' => $data['reject_reason'],
                            ]);

                            Notification::make()
                                ->title('Stock usage request rejected')
                                ->body("Reason: {$data['reject_reason']}")
                                ->danger()
                                ->send();
                        }),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record)=> $record->requester_id === auth()->user()->id),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record)=> $record->requester_id === auth()->user()->id && !$record->head_id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Stock Usage Details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('Usage Number'),
                                Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('head.name')
                                    ->label('Approved By')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Stock Usage Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Item Name'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Quantity'),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('-'),
                                    ]),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStockUsages::route('/'),
        ];
    }
}
