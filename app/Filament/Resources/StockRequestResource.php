<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockRequestResource\Pages;
use App\Filament\Resources\StockRequestResource\RelationManagers;
use App\Models\StockRequest;
use App\Models\CompanyDivision;
use App\Models\item;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class StockRequestResource extends Resource
{
    protected static ?string $model = StockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationLabel = 'Stock Requests';
    protected static ?string $navigationParentItem = 'Stocks';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                        Forms\Components\TextInput::make('division_id')
                            ->label('Division')
                            ->columnSpanFull()
                            ->default(auth()->user()->division?->name)
                            ->disabled(),
                        Forms\Components\Hidden::make('type')
                            ->default(StockRequest::TYPE_INCREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Request Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('item.category', 'name')
                                    ->searchable()
                                    ->reactive()
                                    ->preload()
                                    ->dehydrated(false),
                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('category_id');
                                        if (!$categoryId) {
                                            return [];
                                        }
                                        return \App\Models\OfficeStationeryItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => StockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        StockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => StockRequest::STATUS_PENDING,
                        'success' => [StockRequest::STATUS_APPROVED_BY_HEAD, StockRequest::STATUS_APPROVED_BY_IPC, StockRequest::STATUS_DELIVERED, StockRequest::STATUS_COMPLETED],
                        'danger' => [StockRequest::STATUS_REJECTED_BY_HEAD, StockRequest::STATUS_REJECTED_BY_IPC],
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        StockRequest::STATUS_PENDING => 'Pending',
                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        StockRequest::STATUS_DELIVERED => 'Delivered',
                        StockRequest::STATUS_COMPLETED => 'Completed',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        StockRequest::TYPE_INCREASE => 'Stock Increase',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        StockRequest::STATUS_PENDING => 'Pending',
                        StockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        StockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        StockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        StockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        StockRequest::STATUS_DELIVERED => 'Delivered',
                        StockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === StockRequest::STATUS_PENDING && auth()->user()->id === $record->requested_by),
                
                // Approval Actions
                Tables\Actions\Action::make('approve_as_head')
                    ->label('Approve (Head)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === StockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => StockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_head')
                    ->label('Reject (Head)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        $record->status === StockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => StockRequest::STATUS_REJECTED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc')
                    ->label('Approve (IPC)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === StockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => StockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by IPC successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('deliver')
                    ->label('Mark as Delivered')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn ($record) => 
                        $record->canBeDelivered() &&
                        auth()->user()->hasRole('Staff')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Update stock levels
                        foreach ($record->items as $item) {
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::firstOrCreate([
                                'division_id' => $record->division_id,
                                'item_id' => $item->item_id,
                            ], [
                                'current_stock' => 0,
                            ]);
                            $officeStationeryStockPerDivision->increment('current_stock', $item->quantity);
                        }
                        
                        $record->update([
                            'status' => $record->isIncrease() ? StockRequest::STATUS_COMPLETED : StockRequest::STATUS_DELIVERED,
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Request marked as delivered and stock updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Filter based on user role
        if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Head')) {
            $query->where('division_id', auth()->user()->division_id);
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolists\Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Stock Request Detail')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('request_number')
                                    ->label('Request Number'),
                                Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                            ]),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Stock Request Status')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Stock Request Type'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Stock Request Status')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Stock Request Notes')
                                    ->columnSpan(3),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Stock Request Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, ['rejected_by_head', 'rejected_by_ipc']))
                                    ->columnSpan(3),
                                Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Stock Request Head Approval Name')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('ipcStaff.name')
                                    ->label('IPC Approval Name')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Stock Request Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Item Name'),
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Item Category Name'),
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
            'index' => Pages\ListStockRequests::route('/'),
            'view' => Pages\ViewStockRequest::route('/{record}'),
        ];
    }
}
