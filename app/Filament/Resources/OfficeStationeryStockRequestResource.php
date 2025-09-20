<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryStockRequestResource\Pages\MyDivisionOfficeStationeryStockRequest;
use App\Filament\Resources\OfficeStationeryStockRequestResource\Pages\RequestListOfficeStationeryStockRequest;
use App\Helpers\RequestStatusChecker;
use App\Helpers\UserRoleChecker;
use Filament\Forms;
use App\Models\item;
use App\Models\User;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CompanyDivision;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\OfficeStationeryStockRequest;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OfficeStationeryStockRequestResource\RelationManagers;
use App\Filament\Resources\OfficeStationeryStockRequestResource\Pages\ViewOfficeStationeryStockRequest;
use App\Filament\Resources\OfficeStationeryStockRequestResource\Pages\ListOfficeStationeryStockRequests;

class OfficeStationeryStockRequestResource extends Resource
{
    protected static ?string $model = OfficeStationeryStockRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Pemasukan ATK';
    protected static ?string $modelLabel = 'Pemasukan ATK';
    protected static ?string $pluralModelLabel = 'Pemasukan ATK';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->cloneable()
                            ->extraItemActions([
                                Action::make('add_new_after')
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
                                        
                                        $newState = array_slice($state, 0, $currentIndex + 1, true) +
                                                [$newKey => $newItem] +
                                                array_slice($state, $currentIndex + 1, null, true);
                                        
                                        $component->state($newState);
                                    }),
                            ])
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('item.category', 'name')
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
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
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }
                                        
                                        $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return 'No inventory limit set for this item';
                                        }
                                        
                                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
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
                                        
                                        $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        if (!$setting) {
                                            return;
                                        }
                                        
                                        $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
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
                                                
                                                $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', auth()->user()->division_id)
                                                    ->where('item_id', $itemId)
                                                    ->first();
                                                    
                                                if (!$setting) {
                                                    return;
                                                }
                                                
                                                $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', auth()->user()->division_id)
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
                                Forms\Components\Textarea::make('notes')
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
                Forms\Components\Section::make('Pemasukan ATK Information (Optional)')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(OfficeStationeryStockRequest::TYPE_INCREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                // IPC & HCG Admins & Heads can see all requests (for approval workflow)
                if (UserRoleChecker::isIpcAdmin()||UserRoleChecker::isIpcHead()|| UserRoleChecker::isGaAdmin() ||UserRoleChecker::isDivisionHead()) {
                                // Filter records to show only status from Approved by Head IPC (Pre Adjustment) to Completed
                $statuses = [
                    OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                    OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                    OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD,
                    OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD,
                    OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                    OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                    OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                    OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN,
                    OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN,
                    OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD,
                    OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                    OfficeStationeryStockRequest::STATUS_COMPLETED,
                ];
                
                $query->whereIn('status', $statuses)->orderByDesc('created_at')->orderByDesc('request_number');
                }
                
                return $query;
            })
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
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => OfficeStationeryStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN, OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockRequest::STATUS_DELIVERED, OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN => 'Approved by GA Admin (Second)',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN => 'Rejected by GA Admin (Second)',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->visible(fn() => UserRoleChecker::isInDivisionWithInitial(['IPC', 'GA'] && UserRoleChecker::hasRole(['Admin', 'Head'])))
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
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
                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN => 'Approved by GA Admin (Second)',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN => 'Rejected by GA Admin (Second)',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Approval Actions
                Tables\Actions\EditAction::make('adjust_and_approve_stock')
                    ->label('Adjust & Approve Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Penyesuaian Stok Pemasukan ATK')
                    ->modalSubheading('Apakah Anda yakin ingin melakukan penyesuaian stok untuk Pemasukan ATK ini?')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->visible(fn ($record) =>
                        RequestStatusChecker::atkStockRequestNeedStockAdjustmentApprovalFromIpcAdmin($record))
                    ->form(function ($record) {
                        return [
                            Forms\Components\Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('item_name')
                                        ->label('Item')
                                        ->disabled()
                                        ->extraInputAttributes(['class' => 'whitespace-normal'])
                                        ->formatStateUsing(fn ($record) => $record?->item?->name ?? ''),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Requested Quantity')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('adjusted_quantity')
                                        ->label('Adjusted Quantity')
                                        ->numeric()
                                        ->minValue(0)
                                        ->required()
                                        ->default(fn ($record) => $record?->quantity ?? 0)
                                        ->helperText(function (callable $get, $record) {
                                            if (!$record) {
                                                return '';
                                            }
                                            
                                            $itemId = $record->item_id;
                                            if (!$itemId) {
                                                return '';
                                            }
                                            
                                            $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', $record->stockRequest->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();
                                                
                                            if (!$setting) {
                                                return 'No inventory limit set for this item';
                                            }
                                            
                                            $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->stockRequest->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();
                                                
                                            $currentStock = $stock ? $stock->current_stock : 0;
                                            $maxLimit = $setting->max_limit;
                                            $availableSpace = $maxLimit - $currentStock;
                                            
                                            return "Current: {$currentStock} | Max: {$maxLimit} | Available: {$availableSpace}";
                                        })
                                        ->live()
                                        ->afterStateUpdated(function (callable $get, callable $set, $state, $record) {
                                            if (!$record || !$state) {
                                                return;
                                            }
                                            
                                            $itemId = $record->item_id;
                                            if (!$itemId) {
                                                return;
                                            }
                                            
                                            $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', $record->stockRequest->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();
                                                
                                            if (!$setting) {
                                                return;
                                            }
                                            
                                            $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->stockRequest->division_id)
                                                ->where('item_id', $itemId)
                                                ->first();
                                                
                                            $currentStock = $stock ? $stock->current_stock : 0;
                                            $maxLimit = $setting->max_limit;
                                            $availableSpace = $maxLimit - $currentStock;
                                            
                                            if ($state > $availableSpace) {
                                                // Reset to available space
                                                $set('adjusted_quantity', $availableSpace);
                                                
                                                // Show notification to user
                                                Notification::make()
                                                    ->title('Kuantitas melebih batas maksimal')
                                                    ->body("Kuantitas penyesuaian melebihi batas maksimal, maksimal kuantitas yang bisa diminta: {$availableSpace}")
                                                    ->warning()
                                                    ->send();
                                            }
                                        })
                                        ->rules([
                                            function ($record) {
                                                return function (string $attribute, $value, \Closure $fail) use ($record) {
                                                    if (!$record || !$value) {
                                                        return;
                                                    }
                                                    
                                                    $itemId = $record->item_id;
                                                    if (!$itemId) {
                                                        return;
                                                    }
                                                    
                                                    $setting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', $record->stockRequest->division_id)
                                                        ->where('item_id', $itemId)
                                                        ->first();
                                                        
                                                    if (!$setting) {
                                                        return;
                                                    }
                                                    
                                                    $stock = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->stockRequest->division_id)
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
                                ])
                                ->columns(3)
                                ->disabled(fn () => !$record->needsStockAdjustmentApproval())
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->required()
                        ];
                    })
                    ->successNotification(
                        Notification::make()
                            ->title('Pemasukan ATK penyesuaian stok berhasil di-approve!')
                            ->success()
                    )
                    ->mutateFormDataUsing(function (array $data, $record) {
                        // Validate adjusted quantities against maximum limits
                        $validationErrors = [];
                        foreach ($record->items as $index => $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            // Get adjusted quantity from form data
                            $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                            
                            // Get Current and maximum limit
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            $divisionInventorySetting = \App\Models\OfficeStationeryDivisionInventorySetting::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            // Calculate new stock level
                            $currentStock = $officeStationeryStockPerDivision->current_stock ?? 0;
                            $newStock = $currentStock + $adjustedQuantity;
                            
                            // Check against maximum limit
                            if ($divisionInventorySetting && $newStock > $divisionInventorySetting->max_limit) {
                                $validationErrors[] = "Item {$item->item->name} melebihi batas maksimal yaitu {$divisionInventorySetting->max_limit} units (kuantitas baru stok yaitu {$newStock} unit).";
                            }
                        }
                        
                        // If there are validation errors, display them and stop the process
                        if (!empty($validationErrors)) {
                            Notification::make()
                                ->title('Penyesuaian stok melebihi batas maksimum')
                                ->body(implode("", $validationErrors))
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Save adjusted quantities
                        foreach ($record->items as $index => $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            // Get adjusted quantity from form data
                            $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                            $item->adjusted_quantity = $adjustedQuantity;
                            $item->save();
                        }
                        
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        return $data;
                    }),
                
                Tables\Actions\Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        RequestStatusChecker::atkStockRequestNeedApprovalFromIpcHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_second_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        RequestStatusChecker::atkStockRequestNeedSecondApprovalFromGaAdmin($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN,
                            'approval_second_ga_admin_id' => auth()->user()->id,
                            'approval_second_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) =>
                        RequestStatusChecker::atkStockRequestNeedApprovalFromHcgHead($record))
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        // Update stock levels with adjusted quantities
                        foreach ($record->items as $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                            
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->lockForUpdate()
                                ->first();
                                
                            if ($officeStationeryStockPerDivision) {
                                // Store previous stock
                                $item->previous_stock = $officeStationeryStockPerDivision->current_stock;
                                
                                // Update stock
                                $officeStationeryStockPerDivision->increment('current_stock', $adjustedQuantity);
                                
                                // Store new stock
                                $item->new_stock = $officeStationeryStockPerDivision->current_stock;
                                $item->save();
                            }
                        }
                        
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_COMPLETED,
                            'approval_hcg_head_id' => auth()->user()->id,
                            'approval_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                            // Automatically mark as delivered
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-approve dan stok item berhasil diperbaharui!')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        RequestStatusChecker::atkStockRequestNeedApprovalFromHcgHead($record))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_hcg_head_id' => auth()->user()->id,
                            'rejection_hcg_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-reject!')
                            ->warning()
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
                Infolists\Components\Section::make('Pemasukan ATK Detail')
                    ->schema([
                        Infolists\Components\Grid::make(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('request_number')
                                    ->label('Request Number'),
                                Infolists\Components\TextEntry::make('requester.name')
                                    ->label('Requester Name'),
                                Infolists\Components\TextEntry::make('division.name')
                                    ->label('Division Name'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Stock Increase',
                                        default => ucfirst($state),
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::TYPE_INCREASE => 'primary',
                                        default => 'secondary',
                                    }),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->persistCollapsed()
                    ->id('stock-request-detail'),
                    
                Infolists\Components\Section::make('Pemasukan ATK Status')
                    ->schema([
                        Infolists\Components\Grid::make(6)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT => 'Stock Adjustment Approved',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD => 'Approved by GA Head',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD => 'Rejected by GA Head',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN => 'Approved by GA Admin (Second)',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN => 'Rejected by GA Admin (Second)',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                                        OfficeStationeryStockRequest::STATUS_DELIVERED => 'Delivered',
                                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                                        default => ucfirst(str_replace('_', ' ', $state)),
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_HEAD,
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_GA_ADMIN,
                                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD,
                                        OfficeStationeryStockRequest::STATUS_DELIVERED,
                                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD,
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN,
                                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
                                        default => 'secondary',
                                    })
                                    ->columnSpan(6),
                                Infolists\Components\TextEntry::make('divisionHead.name')
                                    ->label('Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_head_at')
                                    ->label('Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionHead.name')
                                    ->label('Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_head_at')
                                    ->label('Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_head_id !== null),
                                Infolists\Components\TextEntry::make('ipcAdmin.name')
                                    ->label('IPC Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_at')
                                    ->label('IPC Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpc.name')
                                    ->label('IPC Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_at')
                                    ->label('IPC Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_id !== null),
                                Infolists\Components\TextEntry::make('ipcHead.name')
                                    ->label('IPC Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ipc_head_at')
                                    ->label('IPC Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionIpcHead.name')
                                    ->label('IPC Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ipc_head_at')
                                    ->label('IPC Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ipc_head_id !== null),
                                Infolists\Components\TextEntry::make('approvalStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Approved By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('approval_stock_adjustment_at')
                                    ->label('Stock Adjustment Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejectionStockAdjustmentBy.name')
                                    ->label('Stock Adjustment Rejected By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('rejection_stock_adjustment_at')
                                    ->label('Stock Adjustment Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_stock_adjustment_id !== null),
                                Infolists\Components\TextEntry::make('gaAdmin.name')
                                    ->label('GA Admin Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_admin_at')
                                    ->label('GA Admin Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                                    ->label('GA Admin Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                                    ->label('GA Admin Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                                Infolists\Components\TextEntry::make('gaHead.name')
                                    ->label('GA Head Approve')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('approval_ga_head_at')
                                    ->label('GA Head Approval At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->approval_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejectionGaHead.name')
                                    ->label('GA Head Reject')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('rejection_ga_head_at')
                                    ->label('GA Head Rejection At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->rejection_ga_head_id !== null),
                                Infolists\Components\TextEntry::make('deliverer.name')
                                    ->label('Completed By')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->delivered_by !== null && $record->status === OfficeStationeryStockRequest::STATUS_COMPLETED),
                                Infolists\Components\TextEntry::make('delivered_at')
                                    ->label('Completed At')
                                    ->dateTime()
                                    ->placeholder('-')
                                    ->visible(fn ($record) => $record->delivered_by !== null && $record->status === OfficeStationeryStockRequest::STATUS_COMPLETED),
                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->visible(fn ($record) => in_array($record->status, [OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_GA_ADMIN, OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD]))
                                    ->columnSpan(6),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-request-status'),

                Infolists\Components\Section::make('Pemasukan ATK Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\Grid::make(5)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('item.name')
                                            ->label('Name'),
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Requested Quantity'),
                                        Infolists\Components\TextEntry::make('adjusted_quantity')
                                            ->label('Adjusted Quantity')
                                            ->visible(fn ($record) => $record->adjusted_quantity !== null),
                                        Infolists\Components\TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('-'),
                                    ]),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->persistCollapsed()
                    ->id('stock-request-items'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficeStationeryStockRequests::route('/'),
            'my-division' => MyDivisionOfficeStationeryStockRequest::route('my-division'),
            'request-list' => RequestListOfficeStationeryStockRequest::route('request-list'),
            'view' => ViewOfficeStationeryStockRequest::route('/{record}'),
        ];
    }
}
