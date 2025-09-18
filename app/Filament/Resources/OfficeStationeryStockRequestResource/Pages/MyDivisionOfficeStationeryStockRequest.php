<?php

namespace App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Models\OfficeStationeryItem;
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
use App\Models\OfficeStationeryStockRequest;
use App\Models\OfficeStationeryStockPerDivision;
use App\Models\OfficeStationeryDivisionInventorySetting;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\OfficeStationeryStockRequestResource;
use App\Helpers\RequestStatusChecker;
use App\Helpers\UserRoleChecker;

class MyDivisionOfficeStationeryStockRequest extends ListRecords
{
    protected static string $resource = OfficeStationeryStockRequestResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Permintaan ATK (Divisi Saya)';
    protected static ?string $modelLabel = 'Permintaan ATK (Divisi Saya)';
    protected static ?string $pluralModelLabel = 'Permintaan ATK (Divisi Saya)';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah')
                ->mutateFormDataUsing(function (array $data) {
                    $data['division_id'] = auth()->user()->division_id;
                    $data['requested_by'] = auth()->user()->id;
                    return $data;
                })
                ->visible(fn() => UserRoleChecker::isDivisionAdmin())
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->successNotification(
                    Notification::make()
                        ->title('Permintaan ATK berhasil dibuat!')
                        ->success()
                ),
        ];
    }

    public function getBreadcrumb(): string
    {
        return 'Permintaan ATK (Divisi Saya)';
    }

    public function getTitle(): string
    {
        return 'Permintaan ATK (Divisi Saya)';
    }
    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = OfficeStationeryStockRequest::query()->where('division_id', $user->division_id)->orderByDesc('request_number')->orderByDesc('created_at');

        return $table
            ->modifyQueryUsing(fn() => $query)
            ->columns([
                TextColumn::make('request_number')->searchable()->sortable(),
                TextColumn::make('division.name')->searchable()->sortable(),
                TextColumn::make('requester.name')->label('Requested By')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => OfficeStationeryStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            OfficeStationeryStockRequest::TYPE_INCREASE => 'Increase',
                        },
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD, OfficeStationeryStockRequest::STATUS_DELIVERED, OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                            default => 'secondary',
                        },
                    )
                    ->formatStateUsing(
                        fn($state) => match ($state) {
                            OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                            OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                            OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN => 'Approved by GA Admin (Post ADjustment)',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN => 'Rejected by GA Admin (Post ADjustment)',
                            OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                            OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                            OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                        },
                    ),
                TextColumn::make('items_count')->label('Items')->counts('items'),
            ])
            ->filters([
                SelectFilter::make('division_id')->label('Division')->relationship('division', 'name')->searchable()->preload(),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN => 'Approved by GA Admin (Post ADjustment)',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN => 'Rejected by GA Admin (Post ADjustment)',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => UserRoleChecker::getRequesterId($record)),
                DeleteAction::make()
                    ->visible(fn($record) => UserRoleChecker::getRequesterId($record)),
                // Approval Actions up to IPC Head
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);

                        Notification::make()->title('Permintaan ATK berhasil di-approve!')->success()->send();
                    }),

                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                    ->form([Textarea::make('rejection_reason')->required()->maxLength(65535)])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->title('Permintaan ATK berhasil di-reject!')->warning()->send();
                    }),

                EditAction::make('resubmit_request')
                    ->label('Resubmit')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn($record) => RequestStatusChecker::canResubmitOfficeStationeryStockRequest($record) && UserRoleChecker::getRequesterId($record))
                    ->form([
                        Section::make('Rejection Information')
                            ->schema([
                                TextInput::make('rejected_by')
                                    ->label('Rejected By')
                                    ->disabled()
                                    ->formatStateUsing(function ($record) {
                                        if (RequestStatusChecker::atkStockRequestRejectedByDivHead($record)) {
                                            return $record->rejectionHead->name ?? '';
                                        } elseif (RequestStatusChecker::atkStockRequestRejectedByGaAdmin($record)) {
                                            return $record->rejectionGaAdmin->name ?? '';
                                        } elseif (RequestStatusChecker::atkStockRequestRejectedByGaHead($record)) {
                                            return $record->rejectionGaHead->name ?? '';
                                        } 
                                        return '';
                                    }),
                                Textarea::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->maxLength(65535)
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->visible(fn($record) => RequestStatusChecker::stockRequestIsRejected($record)),
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
                                            return OfficeStationeryItem::where('category_id', $categoryId)->pluck('name', 'id');
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

                                            $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            if (!$setting) {
                                                return 'No inventory limit set for this item';
                                            }

                                            $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
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

                                            $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            if (!$setting) {
                                                return;
                                            }

                                            $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
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

                                                    $setting = OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                        ->where('item_id', $itemId)
                                                        ->first();

                                                    if (!$setting) {
                                                        return;
                                                    }

                                                    $stock = OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
                                                        ->where('item_id', $itemId)
                                                        ->first();

                                                    $currentStock = $stock ? $stock->current_stock : 0;
                                                    $maxLimit = $setting->max_limit;
                                                    $availableSpace = $maxLimit - $currentStock;

                                                    if ($value > $availableSpace) {
                                                        $fail("Kuantitas yang diminta ({$value}) melebihi batas maksimal yaitu ({$availableSpace}) untuk item ini.");
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
                        Section::make('Pemasukan ATK Information (Optional)')
                            ->schema([Hidden::make('type')->default(OfficeStationeryStockRequest::TYPE_INCREASE), Textarea::make('notes')->maxLength(6535)->columnSpanFull()])
                            ->columns(2),
                    ])
                    ->action(function ($record, array $data) {
                        // Update the record with new data
                        $record->update($data);

                        // Reset status to pending and clear rejection information
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_PENDING,
                            'rejection_head_id' => null,
                            'rejection_head_at' => null,
                            'rejection_ipc_id' => null,
                            'rejection_ipc_at' => null,
                            'rejection_ipc_head_id' => null,
                            'rejection_ipc_head_at' => null,
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Permintaan ATK berhasil diresubmit!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make('delete_selected')
                        ->label('Delete Selected')
                        ->visible(fn() => UserRoleChecker::isDivisionAdmin())
                        ->successNotificationTitle('Permintaan ATK yang terpilih berhasil dihapus!')
                ])
            ]);
    }
}
