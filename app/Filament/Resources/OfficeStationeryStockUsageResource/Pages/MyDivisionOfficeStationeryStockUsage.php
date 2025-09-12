<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Models\OfficeStationeryItem;
use Filament\Support\Enums\MaxWidth;
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
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use App\Models\OfficeStationeryStockUsage;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Models\OfficeStationeryStockPerDivision;
use App\Models\OfficeStationeryDivisionInventorySetting;
use Filament\Forms\Components\Actions\Action as FormAction;
use App\Filament\Resources\OfficeStationeryStockUsageResource;
use App\Helpers\RequestStatusChecker;
use App\Helpers\UserRoleChecker;

class MyDivisionOfficeStationeryStockUsage extends ListRecords
{
    protected static string $resource = OfficeStationeryStockUsageResource::class;
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Pengeluaran ATK (Divisi Saya)';
    protected static ?string $modelLabel = 'Pengeluaran ATK (Divisi Saya)';
    protected static ?string $pluralModelLabel = 'Pengeluaran ATK (Divisi Saya)';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function(array $data){
                    $data['requested_by'] = auth()->user()->id;
                    $data['division_id'] = auth()->user()->division_id;
                    return $data;
                })
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->label('Tambah'),
        ];
    }
    
    public function getBreadcrumb(): string
    {
        return 'Pengeluaran ATK (Divisi Saya)';
    }

    public function getTitle(): string
    {
        return 'Pengeluaran ATK (Divisi Saya)';
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = OfficeStationeryStockUsage::query()->where('division_id', $user->division_id)->orderByDesc('usage_number')->orderByDesc('created_at');

        return $table
            ->modifyQueryUsing(fn() => $query)
            ->columns([
                TextColumn::make('usage_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'Decrease',
                        default => ucfirst($state),
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'warning',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockUsage::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
                    }),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        OfficeStationeryStockUsage::TYPE_DECREASE => 'Decrease',
                    ]),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->authorize(true), // Bypass authorization for view action
                EditAction::make()
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockUsage::STATUS_PENDING && UserRoleChecker::getRequesterId($record)),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === OfficeStationeryStockUsage::STATUS_PENDING && UserRoleChecker::getRequesterId($record)),
                EditAction::make('resubmit_request')
                    ->label('Resubmit')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn($record) => RequestStatusChecker::canResubmitOfficeStationeryStockUsage($record) && UserRoleChecker::getRequesterId($record))
                    ->form([
                        Grid::make(1)->schema([
                            Section::make('Rejection Information')
                                ->schema([
                                    TextInput::make('rejected_by')
                                        ->label('Rejected By')
                                        ->disabled()
                                        ->formatStateUsing(function ($record) {
                                            if ($record->rejection_head_id) {
                                                return $record->rejectionHead->name ?? '';
                                            } elseif ($record->rejection_ga_admin_id) {
                                                return $record->rejectionGaAdmin->name ?? '';
                                            } elseif ($record->rejection_hcg_head_id) {
                                                return $record->rejectionHcgHead->name ?? '';
                                            }
                                            return '';
                                        }),
                                    Textarea::make('rejection_reason')
                                        ->label('Rejection Reason')
                                        ->maxLength(65535)
                                        ->disabled(),
                                ])
                                ->columns(2)
                                ->visible(fn ($record) => RequestStatusChecker::stockUsageIsRejected($record)),
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
                                    Select::make('category_id')
                                        ->label('Category')
                                        ->relationship('item.category', 'name')
                                        ->searchable()
                                        ->reactive()
                                        ->preload(),
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

                                            return "Current: {$currentStock}";
                                        })
                                        ->live()
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

                                                    // Get division_id from the record
                                                    $record = OfficeStationeryStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;

                                                    $stock = OfficeStationeryStockPerDivision::where('division_id', $divisionId)
                                                        ->where('item_id', $itemId)
                                                        ->first();

                                                    $currentStock = $stock ? $stock->current_stock : 0;

                                                    if ($value > $currentStock) {
                                                        $fail("The requested quantity ({$value}) exceeds the available stock ({$currentStock}) for this item.");
                                                    }
                                                };
                                            },
                                        ]),
                                    Textarea::make('notes')
                                        ->maxLength(1000)
                                        ->rows(1)
                                        ->autosize(),
                                ])
                                ->columns(4)
                                ->minItems(1)
                                ->addActionLabel('Add Item')
                                ->reorderableWithButtons()
                                ->collapsible(),
                        ]),
                        Section::make('Pengeluaran ATK Information (Optional)')
                            ->schema([
                                Hidden::make('type')
                                    ->default(OfficeStationeryStockUsage::TYPE_DECREASE),
                                Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->columnSpanFull()
                            ])
                            ->columns(2),
                    ])
                    ->action(function ($record, array $data) {
                        // Update the record with new data
                        $record->update($data);

                        // Reset status to pending and clear rejection information
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_PENDING,
                            'rejection_head_id' => null,
                            'rejection_head_at' => null,
                            'rejection_ga_admin_id' => null,
                            'rejection_ga_admin_at' => null,
                            'rejection_hcg_head_id' => null,
                            'rejection_hcg_head_at' => null,
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Pengeluaran ATK berhasil diresubmit!')
                            ->success()
                            ->send();
                    }),
                // Approval Actions
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_PENDING &&
                        UserRoleChecker::isDivisionHead() &&
                        UserRoleChecker::canApproveInDivision($record)
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_PENDING &&
                        UserRoleChecker::isDivisionHead() &&
                        UserRoleChecker::canApproveInDivision($record)
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-reject!')
                            ->success()
                            ->send();
                    }),
                Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD &&
                        (UserRoleChecker::isDivisionAdmin() && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD &&
                        (UserRoleChecker::isDivisionAdmin() && UserRoleChecker::isInDivisionWithInitial('GA'))
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-reject!')
                            ->success()
                            ->send();
                    }),
                Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN &&
                        (UserRoleChecker::isDivisionHead() && UserRoleChecker::isInDivisionWithInitial('HCG'))
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_COMPLETED,
                            'approval_hcg_head_id' => auth()->user()->id,
                            'approval_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        // Process the Pengeluaran ATK
                        $record->processStockUsage();
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-approve dan stok item berhasil diperbaharui!')
                            ->success()
                            ->send();
                    }),
                Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN &&
                        (UserRoleChecker::isDivisionHead() && UserRoleChecker::isInDivisionWithInitial('MKS'))
                    )
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_hcg_head_id' => auth()->user()->id,
                            'rejection_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-reject!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
