<?php

namespace App\Filament\Resources;

use App\Models\Budget;
// Remove this line - we don't need ItemPrice anymore
use App\Services\BudgetService;
use Filament\Forms;
use Filament\Tables;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CompanyDivision;
use App\Helpers\UserRoleChecker;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Helpers\RequestStatusChecker;
use App\Models\MarketingMediaStockUsage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MarketingMediaStockUsageResource\Pages;
use App\Filament\Resources\MarketingMediaStockUsageResource\RelationManagers;

class MarketingMediaStockUsageResource extends Resource
{
    protected static ?string $model = MarketingMediaStockUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?string $navigationLabel = 'Pengeluaran Media Cetak';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Usage Information')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MarketingMediaStockUsage::TYPE_DECREASE),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Usage Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
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
                                        return \App\Models\MarketingMediaItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Update category when item is selected
                                        if ($state) {
                                            $item = \App\Models\MarketingMediaItem::find($state);
                                            if ($item && !$get('category_id')) {
                                                $set('category_id', $item->category_id);
                                            }
                                        }
                                        
                                        // Calculate total cost and check budget
                                        $items = $get('../../items');
                                        $totalCost = 0;
                                        
                                        foreach ($items as $itemData) {
                                            if (isset($itemData['item_id']) && isset($itemData['quantity'])) {
                                                $item = \App\Models\MarketingMediaItem::find($itemData['item_id']);
                                                if ($item) {
                                                    $itemPrice = \App\Models\MarketingMediaItemPrice::where('item_id', $item->id)
                                                        ->where(function ($query) {
                                                            $query->whereNull('end_date')
                                                                  ->orWhere('end_date', '>', now());
                                                        })
                                                        ->orderBy('effective_date', 'desc')
                                                        ->first();
                                                    
                                                    if ($itemPrice) {
                                                        $totalCost += $itemPrice->price * $itemData['quantity'];
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Get remaining budget for Marketing Media
                                        $budgetService = new BudgetService();
                                        $remainingBudget = $budgetService->getRemainingBudget(auth()->user()->division_id, 'Marketing Media');
                                        
                                        // Calculate new budget after usage
                                        $newBudget = $remainingBudget - $totalCost;
                                        
                                        // Show warning if budget is insufficient
                                        if ($newBudget < 0) {
                                            Notification::make()
                                                ->title('Budget Warning')
                                                ->body("The total cost (Rp " . number_format($totalCost, 2) . ") exceeds your remaining budget (Rp " . number_format($remainingBudget, 2) . ").")
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
                                                
                                                // Get division_id from the form or from the record
                                                $divisionId = null;
                                                if (request()->routeIs('filament.dashboard.resources.marketing-media-stock-usages.create')) {
                                                    $divisionId = auth()->user()->division_id;
                                                } else {
                                                    $record = MarketingMediaStockUsage::find(request()->route('record'));
                                                    $divisionId = $record ? $record->division_id : auth()->user()->division_id;
                                                }
                                                
                                                $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', $divisionId)
                                                    ->where('item_id', $itemId)
                                                    ->first();
                                                
                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                
                                                if ($value > $currentStock) {
                                                    $fail("The requested quantity ({$value}) exceeds the available stock ({$currentStock}) for this item.");
                                                }
                                            };
                                        },
                                    ]),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (!$itemId) {
                                            return '';
                                        }
                                        
                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', auth()->user()->division_id)
                                            ->where('item_id', $itemId)
                                            ->first();
                                            
                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        
                                        return "Current stock: {$currentStock}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, $state, callable $set) {
                                        $itemId = $get('item_id');
                                        if (!$itemId || !$state) return;

                                        $divisionId = request()->routeIs('filament.dashboard.resources.marketing-media-stock-usages.create')
                                            ? auth()->user()->division_id
                                            : optional(MarketingMediaStockUsage::find(request()->route('record')))->division_id ?? auth()->user()->division_id;

                                        $stock = \App\Models\MarketingMediaStockPerDivision::where('division_id', $divisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;

                                        if ($state > $currentStock) {
                                            Notification::make()
                                                ->title('Stock Warning')
                                                ->body("Requested quantity exceeds available stock : ({$currentStock}).")
                                                ->danger()
                                                ->send();

                                            // Optionally reset to max stock
                                            $set('quantity', $currentStock);
                                        }
                                    }),
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
                
                Forms\Components\Section::make('Budget Information')
                    ->schema([
                        Forms\Components\TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->disabled()
                            ->formatStateUsing(function (callable $get) {
                                $items = $get('items') ?? [];
                                $totalCost = 0;
                                
                                foreach ($items as $itemData) {
                                    if (isset($itemData['item_id']) && isset($itemData['quantity'])) {
                                        $item = \App\Models\MarketingMediaItem::find($itemData['item_id']);
                                        if ($item) {
                                            $itemPrice = \App\Models\MarketingMediaItemPrice::where('item_id', $item->id)
                                                ->where(function ($query) {
                                                    $query->whereNull('end_date')
                                                          ->orWhere('end_date', '>', now());
                                                })
                                                ->orderBy('effective_date', 'desc')
                                                ->first();
                                            
                                            if ($itemPrice) {
                                                $totalCost += $itemPrice->price * $itemData['quantity'];
                                            }
                                        }
                                    }
                                }
                                
                                return 'Rp ' . number_format($totalCost, 0);
                            })
                            ->dehydrated(false), // Don't save to database
                        Forms\Components\TextInput::make('remaining_budget')
                            ->label('Remaining Budget')
                            ->disabled()
                            ->formatStateUsing(function () {
                                $budgetService = new BudgetService();
                                $remainingBudget = $budgetService->getRemainingBudget(auth()->user()->division_id, 'Marketing Media');
                                
                                return 'Rp ' . number_format($remainingBudget, 0);
                            })
                            ->dehydrated(false), // Don't save to database
                        Forms\Components\TextInput::make('new_budget')
                            ->label('New Budget')
                            ->disabled()
                            ->formatStateUsing(function (callable $get) {
                                $items = $get('items') ?? [];
                                $totalCost = 0;
                                
                                foreach ($items as $itemData) {
                                    if (isset($itemData['item_id']) && isset($itemData['quantity'])) {
                                        $item = \App\Models\MarketingMediaItem::find($itemData['item_id']);
                                        if ($item) {
                                            $itemPrice = \App\Models\MarketingMediaItemPrice::where('item_id', $item->id)
                                                ->where(function ($query) {
                                                    $query->whereNull('end_date')
                                                          ->orWhere('end_date', '>', now());
                                                })
                                                ->orderBy('effective_date', 'desc')
                                                ->first();
                                            
                                            if ($itemPrice) {
                                                $totalCost += $itemPrice->price * $itemData['quantity'];
                                            }
                                        }
                                    }
                                }
                                
                                $budgetService = new BudgetService();
                                $remainingBudget = $budgetService->getRemainingBudget(auth()->user()->division_id, 'Marketing Media');
                                $newBudget = $remainingBudget - $totalCost;
                                
                                return 'Rp ' . number_format($newBudget, 0);
                            })
                            ->dehydrated(false), // Don't save to database
                    ])
                    ->columns(3)
                    ->visible(function () {
                        // Only show this section if the user's division has a budget
                        $budgetService = new BudgetService();
                        $hasBudget = $budgetService->getRemainingBudget(auth()->user()->division_id, 'Marketing Media');
                        return $hasBudget !== null;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                // GA Admins see only requests with status from Approved by Head to Completed (approval workflow)
                if (UserRoleChecker::isGaAdmin() || UserRoleChecker::isMksHead()) {
                    // Filter records to show only status from Approved by Head to Completed
                    $statuses = [
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD,
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        MarketingMediaStockUsage::STATUS_COMPLETED,
                    ];
                    
                    $query->whereIn('status', $statuses)->orderByDesc('created_at')->orderByDesc('usage_number');
                }
                return $query;

            })
            ->columns([
                Tables\Columns\TextColumn::make('usage_number')
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
                        'danger' => MarketingMediaStockUsage::TYPE_DECREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MarketingMediaStockUsage::STATUS_PENDING => 'warning',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD, MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD, MarketingMediaStockUsage::STATUS_COMPLETED => 'success',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Support Head',
                        MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
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
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        MarketingMediaStockUsage::TYPE_DECREASE => 'Stock Decrease',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Approval Actions
                Tables\Actions\Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromGaAdmin($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromGaAdmin($record))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-reject!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve_as_marketing_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromHcgHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD,
                            'approval_marketing_head_id' => auth()->user()->id,
                            'approval_marketing_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        // Process the Pengeluaran ATK
                        $record->processStockUsage();
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-approve dan stok item berhasil diperbaharui!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject_as_marketing_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockUsageNeedApprovalFromHcgHead($record))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD,
                            'rejection_marketing_head_id' => auth()->user()->id,
                            'rejection_marketing_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pengeluaran ATK berhasil di-reject!')
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
        $user = auth()->user();
        if($user->division?->initial === 'GA' && $user->hasRole('Admin')){
            $query->where('status', MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD);
        }elseif($user->division?->initial === 'MKS' && $user->hasRole('Head')){
            $query->where('status', MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN);
        }else{
            // For Marketing divisions, only show their own usages
            if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
                $query->where('division_id', $user->division_id);
            }
            // For IPC, GA divisions, they can see all usages for approval process
            else if ($user->division && 
                     ($user->division->initial === 'IPC' || 
                      $user->division->initial === 'GA')) {
                // These divisions can see all usages for approval
            }
            // For other divisions, only show their own usages
            else {
                $query->where('division_id', $user->division_id);
            }
            $query->orderByDesc('usage_number');
        }
        
        return $query;
    }
    
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow Marketing divisions to view their own usages
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Admin']);
        }
        
        // Allow IPC, GA divisions to view all usages for approval process
        if ($user->division && 
            ($user->division->initial === 'IPC' || $user->division->initial === 'GA')) {
            return $user->hasRole(['Admin', 'Head', 'Admin']);
        }
        
        // Hide from users who don't belong to any Marketing divisions
        return false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Only allow Marketing divisions to create usages
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Admin']);
        }
        
        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow editing if user belongs to the same division as the record and is from a Marketing division
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head', 'Admin']) && 
                   $user->division_id === $record->division_id;
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Allow deleting if user belongs to the same division as the record and is from a Marketing division
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin']) && 
                   $user->division_id === $record->division_id;
        }
        
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Usage Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('usage_number'),
                        Infolists\Components\TextEntry::make('requester.name')
                            ->label('Requested By'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                MarketingMediaStockUsage::TYPE_DECREASE => 'danger',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                MarketingMediaStockUsage::TYPE_DECREASE => 'Decrease',
                                default => ucfirst($state),
                            }),
                        Infolists\Components\TextEntry::make('notes'),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-detail'),
                Infolists\Components\Section::make('Pengeluaran Media Cetak Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                MarketingMediaStockUsage::STATUS_PENDING => 'warning',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD, MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'success',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'danger',
                                MarketingMediaStockUsage::STATUS_COMPLETED => 'success',
                                default => 'secondary',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                MarketingMediaStockUsage::STATUS_PENDING => 'Pending',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                MarketingMediaStockUsage::STATUS_COMPLETED => 'Completed',
                                default => ucfirst(str_replace('_', ' ', $state)),
                            }),
                        Infolists\Components\TextEntry::make('divisionHead.name')
                            ->label('Head Approve')
                            ->visible(fn ($record) => $record->approval_head_id !== null),
                        Infolists\Components\TextEntry::make('approval_head_at')
                            ->label('Head Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_head_id !== null),
                        Infolists\Components\TextEntry::make('rejectionHead.name')
                            ->label('Head Rejection')
                            ->visible(fn ($record) => $record->rejection_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_head_at')
                            ->label('Head Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_head_id !== null),
                        Infolists\Components\TextEntry::make('gaAdmin.name')
                            ->label('GA Admin Approve')
                            ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('approval_ga_admin_at')
                            ->label('GA Admin Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('rejectionGaAdmin.name')
                            ->label('GA Admin Rejection')
                            ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('rejection_ga_admin_at')
                            ->label('GA Admin Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_ga_admin_id !== null),
                        Infolists\Components\TextEntry::make('supervisorMarketing.name')
                            ->label('HCG Head Approve')
                            ->visible(fn ($record) => $record->approval_marketing_head_id !== null),
                        Infolists\Components\TextEntry::make('approval_marketing_head_at')
                            ->label('HCG Head Approve At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->approval_marketing_head_id !== null),
                        Infolists\Components\TextEntry::make('rejectionSupervisorMarketing.name')
                            ->label('HCG Head Rejection')
                            ->visible(fn ($record) => $record->rejection_marketing_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_marketing_head_at')
                            ->label('HCG Head Rejection At')
                            ->dateTime()
                            ->visible(fn ($record) => $record->rejection_marketing_head_id !== null),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($record) => in_array($record->status, [MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD, MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN, MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD]))
                            ->columnSpan(6),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('stock-usage-status'),
                
                Infolists\Components\Section::make('Pengeluaran Media Cetak Items')
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
                                            ->label('Quantity'),
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
            'index' => Pages\ListMarketingMediaStockUsages::route('/'),
            'view' => Pages\ViewMarketingMediaStockUsage::route('/{record}'),
            'my-division' => Pages\MyDivisionMarketingMediaStockUsage::route('/my-division'),
        ];
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate the total cost of the usage
        $totalCost = 0;
        $usageItems = $data['items'] ?? [];
        
        foreach ($usageItems as $itemData) {
            $item = \App\Models\MarketingMediaItem::find($itemData['item_id']);
            if ($item) {
                $itemPrice = \App\Models\MarketingMediaItemPrice::where('item_id', $item->id)
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>', now());
                    })
                    ->orderBy('effective_date', 'desc')
                    ->first();
                
                if ($itemPrice) {
                    $totalCost += $itemPrice->price * $itemData['quantity'];
                }
            }
        }
        
        // Check if the division has sufficient budget for Marketing Media
        $budgetService = new BudgetService();
        $hasSufficientBudget = $budgetService->hasSufficientBudget(
            auth()->user()->division_id,
            'Marketing Media',
            $totalCost
        );
        
        if (!$hasSufficientBudget) {
            throw new \Exception('Insufficient budget for this usage. Please check your available budget.');
        }
        
        return $data;
    }
}