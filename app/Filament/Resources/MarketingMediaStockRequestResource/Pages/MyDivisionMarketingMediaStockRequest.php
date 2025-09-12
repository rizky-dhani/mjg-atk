<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Models\MarketingMediaItem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockPerDivision;
use App\Models\MarketingMediaDivisionInventorySetting;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\MarketingMediaStockRequestResource;

class MyDivisionMarketingMediaStockRequest extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?string $navigationLabel = 'Permintaan Media Cetak (Divisi Saya)';
    protected static ?string $modelLabel = 'Permintaan Media Cetak (Divisi Saya)';
    protected static ?string $pluralModelLabel = 'Permintaan Media Cetak (Divisi Saya)';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        
        // Only allow users from Marketing divisions to access this page
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head']);
        }
        
        return false;
    }
    
    public function getBreadcrumb(): string
    {
        return 'Permintaan Media Cetak (Divisi Saya)';
    }

    public function getTitle(): string
    {
        return 'Permintaan Media Cetak (Divisi Saya)';
    }
    
    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = MarketingMediaStockRequest::query()->where('division_id', $user->division_id)->orderByDesc('request_number')->orderByDesc('created_at');
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('request_number')->searchable()->sortable(),
                TextColumn::make('division.name')->searchable()->sortable(),
                TextColumn::make('requester.name')->label('Requested By')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => MarketingMediaStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            MarketingMediaStockRequest::TYPE_INCREASE => 'Increase',
                        },
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_DELIVERED, MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                            default => 'secondary',
                        },
                    )
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                            MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                            MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved (Post Adjustment)',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected (Post Adjustment)',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                            MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                        },
                    ),
                TextColumn::make('items_count')->label('Items')->counts('items'),
            ])
            ->filters([
                SelectFilter::make('division_id')->label('Division')->relationship('division', 'name')->searchable()->preload(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        MarketingMediaStockRequest::STATUS_DELIVERED => 'Delivered',
                        MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD => 'Approved (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD => 'Rejected (Post Adjustment)',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                // Approval Actions up to IPC Head
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === MarketingMediaStockRequest::STATUS_PENDING && auth()->user()->hasRole('Head') && auth()->user()->division_id === $record->division_id)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di approve!')->success()->send();
                    }),

                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === MarketingMediaStockRequest::STATUS_PENDING && auth()->user()->hasRole('Head') && auth()->user()->division_id === $record->division_id)
                    ->form([Textarea::make('rejection_reason')->required()->maxLength(65535)])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di reject!')->warning()->send();
                    }),

                Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && $record->isIncrease() && auth()->user()->division?->initial === 'IPC' && auth()->user()->hasRole('Admin'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()->timezone('Asia/Jakarta'),
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di approve!')->success()->send();
                    }),

                Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && $record->isIncrease() && auth()->user()->division?->initial === 'IPC' && auth()->user()->hasRole('Admin'))
                    ->requiresConfirmation()
                    ->form([Textarea::make('rejection_reason')->required()->maxLength(65535)])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di reject!')->warning()->send();
                    }),

                Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->needsIpcHeadApproval() && $record->isIncrease() && auth()->user()->division?->initial === 'IPC' && auth()->user()->hasRole('Head'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di approve!')->success()->send();
                    }),

                Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->needsIpcHeadApproval() && $record->isIncrease() && auth()->user()->division?->initial === 'IPC' && auth()->user()->hasRole('Head'))
                    ->requiresConfirmation()
                    ->form([Textarea::make('rejection_reason')->required()->maxLength(65535)])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->title('Permintaan Media Cetak berhasil di reject!')->warning()->send();
                    }),

                EditAction::make('resubmit_request')
                    ->label('Resubmit')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn($record) => ($record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD || $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC || $record->status === MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD) && auth()->user()->hasRole('Admin') && auth()->user()->division_id === $record->division_id)
                    ->form([
                        Section::make('Rejection Information')
                            ->schema([
                                TextInput::make('rejected_by')
                                    ->label('Rejected By')
                                    ->disabled()
                                    ->formatStateUsing(function($record){
                                        if ($record->rejection_head_id) {
                                            return $record->rejectionHead->name ?? '';
                                        } elseif ($record->rejection_ipc_id) {
                                            return $record->rejectionIpc->name ?? '';
                                        } elseif ($record->rejection_ipc_head_id) {
                                            return $record->rejectionIpcHead->name ?? '';
                                        } elseif ($record->rejection_ga_admin_id) {
                                            return $record->rejectionGaAdmin->name ?? '';
                                        } elseif ($record->rejection_marketing_head_id) {
                                            return $record->rejectionMarketingHead->name ?? '';
                                        }
                                        return '';
                                    }),
                                Textarea::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->maxLength(65535)
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->visible(fn ($record) => $record->rejection_reason || $record->rejection_head_id || $record->rejection_ipc_id || $record->rejection_ipc_head_id || $record->rejection_ga_admin_id || $record->rejection_marketing_head_id),
                        Grid::make(1)->schema([
                            Repeater::make('items')
                                ->addable(false)
                                ->relationship()
                                ->cloneable()
                                ->extraItemActions([
                                    FormAction::make('add_new_after')
                                        ->icon('heroicon-m-plus')
                                        ->color('primary')
                                        ->action(function (array $arguments, Repeater $component) {
                                            $state = $component->getState();
                                            $currentKey = $arguments['item'];

                                            $newKey = uniqid('item_');
                                            // Pre-populate with empty values for proper binding
                                            $newItem = [
                                                'category_id' => null,
                                                'item_id' => null,
                                                'quantity' => null,
                                                'notes' => null,
                                            ];

                                            // Insert at correct position
                                            $keys = array_keys($state);
                                            $currentIndex = array_search($currentKey, $keys);

                                            $newState = array_slice($state, 0, $currentIndex + 1, true) + [$newKey => $newItem] + array_slice($state, $currentIndex + 1, null, true);

                                            $component->state($newState);
                                        }),
                                ])
                                ->schema([
                                    Select::make('category_id')->label('Category')->relationship('item.category', 'name')->searchable()->reactive()->preload(),
                                    Select::make('item_id')
                                        ->label('Item')
                                        ->options(function (callable $get) {
                                            $categoryId = $get('category_id');
                                            if (!$categoryId) {
                                                return [];
                                            }
                                            return MarketingMediaItem::where('category_id', $categoryId)->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live(),
                                    TextInput::make('quantity')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->helperText(function (callable $get) {
                                            $itemId = $get('item_id');
                                            if (!$itemId) {
                                                return '';
                                            }

                                            $setting = MarketingMediaDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            if (!$setting) {
                                                return 'No inventory limit set for this item';
                                            }

                                            $stock = MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            $currentStock = $stock ? $stock->current_stock : 0;
                                            $maxLimit = $setting->max_limit;
                                            $availableSpace = $maxLimit - $currentStock;

                                            return "Current: {$currentStock} | Max: {$maxLimit} | Available: {$availableSpace}";
                                        })
                                        ->live()
                                        ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                            $itemId = $get('item_id');
                                            if (!$itemId || !$state) {
                                                return;
                                            }

                                            $setting = MarketingMediaDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            if (!$setting) {
                                                return;
                                            }

                                            $stock = MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            $currentStock = $stock ? $stock->current_stock : 0;
                                            $maxLimit = $setting->max_limit;
                                            $availableSpace = $maxLimit - $currentStock;

                                            if ($state > $availableSpace) {
                                                // Reset to available space
                                                $set('quantity', $availableSpace);

                                                // Show notification to user
                                                Notification::make()
                                                    ->title('Kuantitas melebih batas maksimal')
                                                    ->body("Kuantitas penyesuaian melebihi batas maksimal, maksimal kuantitas yang bisa diminta: {$availableSpace}")
                                                    ->warning()
                                                    ->send();
                                            }
                                        })
                                        ->rules([
                                            function () {
                                                return function (string $attribute, $value, \Closure $fail, $livewire) {
                                                    // Extract the repeater index from the attribute name
                                                    // e.g., "data.items.0.quantity" -> index 0
                                                    preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);
                                                    $index = $matches[1] ?? null;

                                                    if ($index === null) {
                                                        return;
                                                    }

                                                    // Get the item_id for this repeater item
                                                    $itemId = data_get($livewire, "data.items.{$index}.item_id");

                                                    if (!$itemId || !$value) {
                                                        return;
                                                    }

                                                    $setting = MarketingMediaDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                        ->where('item_id', $itemId)
                                                        ->first();

                                                    if (!$setting) {
                                                        return;
                                                    }

                                                    $stock = MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                                        ->where('item_id', $itemId)
                                                        ->first();

                                                    $currentStock = $stock ? $stock->current_stock : 0;
                                                    $maxLimit = $setting->max_limit;
                                                    $availableSpace = $maxLimit - $currentStock;

                                                    if ($value > $availableSpace) {
                                                        $fail("The requested quantity ({$value}) exceeds the available space ({$availableSpace}) for this item.");
                                                    }
                                                };
                                            },
                                        ]),
                                    Textarea::make('notes')->maxLength(1000)->rows(1)->autosize(),
                                ])
                                ->columns(4)
                                ->minItems(1)
                                ->addActionLabel('Add Item')
                                ->reorderableWithButtons()
                                ->collapsible(),
                        ]),
                        Section::make('Permintaan Media Cetak Information (Optional)')
                            ->schema([Hidden::make('type')->default(MarketingMediaStockRequest::TYPE_INCREASE), Textarea::make('notes')->maxLength(6535)->columnSpanFull()])
                            ->columns(2),
                        ])
                        ->action(function ($record, array $data) {
                            // Update the record with new data
                            $record->update($data);

                            // Reset status to pending and clear rejection information
                            $record->update([
                                'status' => MarketingMediaStockRequest::STATUS_PENDING,
                                'rejection_head_id' => null,
                                'rejection_head_at' => null,
                                'rejection_ipc_id' => null,
                                'rejection_ipc_at' => null,
                                'rejection_ipc_head_id' => null,
                                'rejection_ipc_head_at' => null,
                                'rejection_reason' => null,
                            ]);

                            Notification::make()->title('Permintaan Media Cetak berhasil diresubmit!')->success()->send();
                        }),
                ]);
    }
}