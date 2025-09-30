<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\MarketingMediaStockRequest;
use App\Filament\Resources\MarketingMediaStockRequestResource;

class ViewMarketingMediaStockRequest extends ViewRecord
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->disabled(fn($record) => $record->status !== MarketingMediaStockRequest::STATUS_PENDING)
                ->visible(fn($record) => $record->division_id === auth()->user()->division_id && auth()->user()->hasRole('Admin')),
            Actions\DeleteAction::make()
                ->visible(fn ($record) => auth()->user()->id === $record->requested_by),
            
            // Approval Actions
            Action::make('approve_as_head')
                ->label('Approve (Head)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to approve this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockRequest::STATUS_PENDING && 
                    auth()->user()->hasRole('Head') &&
                    auth()->user()->division_id === $record->division_id
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                        'approval_head_id' => auth()->user()->id,
                        'approval_head_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak approved successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('reject_as_head')
                ->label('Reject (Head)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to reject this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockRequest::STATUS_PENDING && 
                    auth()->user()->hasRole('Head') &&
                    auth()->user()->division_id === $record->division_id
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                        'approval_head_id' => auth()->user()->id,
                        'approval_head_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak rejected successfully')
                        ->warning()
                        ->send();
                }),
            
            Action::make('approve_as_ipc')
                ->label('Approve (IPC)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to approve this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Admin')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
                        'approval_ipc_id' => auth()->user()->id,
                        'approval_ipc_at' => now()
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak approved by IPC successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('reject_as_ipc')
                ->label('Reject (IPC)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pemasukan Media Cetak')
                ->modalSubheading('Are you sure you want to reject this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Admin')
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                        'approval_ipc_id' => auth()->user()->id,
                        'approval_ipc_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak rejected by IPC successfully')
                        ->warning()
                        ->send();
                }),
            
            Action::make('approve_as_ipc_head')
                ->label('Approve (IPC Head)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to approve this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsIpcHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Head')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                        'approval_ipc_head_id' => auth()->user()->id,
                        'approval_ipc_head_at' => now()
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak approved by IPC Head successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('reject_as_ipc_head')
                ->label('Reject (IPC Head)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pemasukan Media Cetak')
                ->modalSubheading('Are you sure you want to reject this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsIpcHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Head')
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_HEAD,
                        'approval_ipc_head_id' => auth()->user()->id,
                        'approval_ipc_head_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak rejected by IPC Head successfully')
                        ->warning()
                        ->send();
                }),
            
            Action::make('adjust_and_approve_stock')
                ->label('Adjust & Approve Stock')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->modalHeading('Pemasukan Media Cetak Adjustment')
                ->modalSubheading('Are you sure to make the adjustment to this Pemasukan Media Cetak?')
                ->modalWidth(MaxWidth::Screen)
                ->visible(fn ($record) => 
                    $record->needsStockAdjustmentApproval() &&
                    auth()->user()->hasRole('Admin') &&
                    auth()->user()->division?->initial === 'IPC'
                )
                ->requiresConfirmation()
                ->form(function ($record) {
                    // Pre-populate the items data
                    $itemsData = [];
                    foreach ($record->items as $item) {
                        $itemsData[] = [
                            'item_name' => $item->item->name ?? '',
                            'quantity' => $item->quantity ?? 0,
                            'adjusted_quantity' => $item->quantity ?? 0,
                        ];
                    }
                    
                    return [
                        Repeater::make('items')
                            ->schema([
                                TextInput::make('item_name')
                                    ->label('Item')
                                    ->disabled()
                                    ->extraInputAttributes(['class' => 'whitespace-normal']),
                                TextInput::make('quantity')
                                    ->label('Requested Quantity')
                                    ->disabled(),
                                TextInput::make('adjusted_quantity')
                                    ->label('Adjusted Quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->disabled(fn () => !$record->needsStockAdjustmentApproval())
                            ->required()
                            ->default($itemsData)
                    ];
                })
                ->action(function ($record, array $data) {
                    // Validate adjusted quantities against maximum limits
                    $validationErrors = [];
                    foreach ($record->items as $index => $item) {
                        if (!$item) {
                            continue;
                        }
                        
                        // Get adjusted quantity from form data
                        $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                        
                        // Get Current and maximum limit
                        $officeStationeryStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('division_id', $record->division_id)
                            ->where('item_id', $item->item_id)
                            ->first();
                            
                        $divisionInventorySetting = \App\Models\MarketingMediaDivisionInventorySetting::where('division_id', $record->division_id)
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
                            ->title('Stock adjustment exceeds maximum limits')
                            ->body(implode("\n", $validationErrors))
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
                        'status' => MarketingMediaStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                        'approval_stock_adjustment_id' => auth()->user()->id,
                        'approval_stock_adjustment_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Stock adjusted and approved successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('approve_as_ga_admin')
                ->label('Approve (GA Admin)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to approve this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsGaAdminApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                    auth()->user()->hasRole('Admin')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                        'approval_ga_admin_id' => auth()->user()->id,
                        'approval_ga_admin_at' => now()
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak approved by GA Admin successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('reject_as_ga_admin')
                ->label('Reject (GA Admin)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pemasukan Media Cetak')
                ->modalSubheading('Are you sure you want to reject this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsGaAdminApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                    auth()->user()->hasRole('Admin')
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                        'approval_ga_admin_id' => auth()->user()->id,
                        'approval_ga_admin_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak rejected by GA Admin successfully')
                        ->warning()
                        ->send();
                }),
            
            Action::make('approve_as_mkt_head')
                ->label('Approve (Marketing Support Head)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pemasukan Media Cetak')
                ->modalSubheading('Are you sure to approve this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsMarketingHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'Marketing Support' &&
                    auth()->user()->hasRole('Head')
                )
                ->requiresConfirmation()
                ->databaseTransaction()
                ->action(function ($record) {
                    // Update stock levels with adjusted quantities
                    foreach ($record->items as $item) {
                        if (!$item) {
                            continue;
                        }
                        
                        $adjustedQuantity = $item->adjusted_quantity ?? $item->quantity;
                        
                        $officeStationeryStockPerDivision = \App\Models\MarketingMediaStockPerDivision::where('division_id', $record->division_id)
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
                        'status' => MarketingMediaStockRequest::STATUS_COMPLETED,
                        'approval_marketing_head_id' => auth()->user()->id,
                        'approval_marketing_head_at' => now(),
                        // Automatically mark as delivered
                        'delivered_by' => auth()->user()->id,
                        'delivered_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak approved by Marketing Support Head, stock updated, and marked as delivered successfully')
                        ->success()
                        ->send();
                }),
            
            Action::make('reject_as_mkt_head')
                ->label('Reject (Marketing Support Head)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pemasukan Media Cetak')
                ->modalSubheading('Are you sure you want to reject this Pemasukan Media Cetak?')
                ->visible(fn ($record) => 
                    $record->needsMarketingHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'Marketing Support' &&
                    auth()->user()->hasRole('Head')
                )
                ->requiresConfirmation()
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                        'approval_marketing_head_id' => auth()->user()->id,
                        'approval_marketing_head_at' => now(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pemasukan Media Cetak rejected by Marketing Support Head successfully')
                        ->warning()
                        ->send();
                }),
        ];
    }
}