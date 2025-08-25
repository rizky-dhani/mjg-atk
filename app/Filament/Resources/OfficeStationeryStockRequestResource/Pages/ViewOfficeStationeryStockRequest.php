<?php

namespace App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;

use Filament\Actions;
use App\Models\OfficeStationeryStockRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\OfficeStationeryStockRequestResource;

class ViewOfficeStationeryStockRequest extends ViewRecord
{
    protected static string $resource = OfficeStationeryStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
            ->visible(fn ($record) => auth()->user()->id === $record->requested_by),
                // Approval Actions
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Stock Request')
                    ->modalSubheading('Are you sure to approve the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                            'approval_head_id' => auth()->user()->id,
                            'approval_head_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Stock Request')
                    ->modalSubheading('Are you sure to reject the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_PENDING && 
                        auth()->user()->hasRole('Head') &&
                        auth()->user()->division_id === $record->division_id
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Stock Request')
                    ->modalSubheading('Are you sure to approve the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Stock Request')
                    ->modalSubheading('Are you sure you want to reject this Stock Request?')
                    ->visible(fn ($record) => 
                        $record->status === OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Stock Request')
                    ->modalSubheading('Are you sure to approve the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Stock Request')
                    ->modalSubheading('Are you sure you want to reject this Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsIpcHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('deliver')
                    ->label('Mark as Delivered')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->modalHeading('Mark Stock Request as Deliver?')
                    ->modalSubheading('Are you sure to mark the Stock Request as Delivered?')
                    ->visible(fn ($record) => 
                        $record->canBeDelivered() &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->databaseTransaction()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_DELIVERED,
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock Request successfully marked as delivered')
                            ->success()
                            ->send();
                    }),
                
                                Action::make('adjust_and_approve_stock')
                    ->label('Adjust & Approve Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Stock Request Adjustment')
                    ->modalSubheading('Are you sure to make the adjustment to the Stock Request?')
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => 
                        $record->needsStockAdjustmentApproval() &&
                        auth()->user()->hasRole('Admin') &&
                        auth()->user()->division?->initial === 'IPC'
                    )
                    ->requiresConfirmation()
                    ->form(function ($record) {
                        // Debug: Log the record and its items
                        \Log::info('Adjust stock action - Record ID: ' . $record->id);
                        \Log::info('Adjust stock action - Items count: ' . $record->items->count());
                        \Log::info('Adjust stock action - Items: ' . $record->items->pluck('id')->join(', '));
                        
                        // Pre-populate the items data
                        $itemsData = [];
                        foreach ($record->items as $item) {
                            $itemsData[] = [
                                'item_name' => $item->item->name ?? '',
                                'quantity' => $item->quantity ?? 0,
                                'adjusted_quantity' => $item->quantity ?? 0,
                            ];
                        }
                        
                        \Log::info('Adjust stock action - Items data: ' . json_encode($itemsData));
                        
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
                        // Debug: Log the data received
                        \Log::info('Adjust stock action - Form data: ' . json_encode($data));
                        
                        // Validate adjusted quantities against maximum limits
                        $validationErrors = [];
                        foreach ($record->items as $index => $item) {
                            if (!$item) {
                                continue;
                            }
                            
                            // Get adjusted quantity from form data
                            $adjustedQuantity = $data['items'][$index]['adjusted_quantity'] ?? $item->quantity;
                            
                            // Get current stock and maximum limit
                            $officeStationeryStockPerDivision = \App\Models\OfficeStationeryStockPerDivision::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            $divisionInventorySetting = \App\Models\DivisionInventorySetting::where('division_id', $record->division_id)
                                ->where('item_id', $item->item_id)
                                ->first();
                                
                            // Calculate new stock level
                            $currentStock = $officeStationeryStockPerDivision->current_stock ?? 0;
                            $newStock = $currentStock + $adjustedQuantity;
                            
                            // Check against maximum limit
                            if ($divisionInventorySetting && $newStock > $divisionInventorySetting->max_limit) {
                                $validationErrors[] = "Item {$item->item->name} would exceed the maximum limit of {$divisionInventorySetting->max_limit} units (new total would be {$newStock} units).";
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
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_STOCK_ADJUSTMENT,
                            'approval_stock_adjustment_id' => auth()->user()->id,
                            'approval_stock_adjustment_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Stock adjusted and approved successfully')
                            ->success()
                            ->send();
                    }),
            
            Action::make('approve_as_second_ipc_head')
                ->label('Approve as IPC Head (Post Adjustment)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Stock Request')
                ->modalSubheading('Are you sure you want to approve this Stock Request after stock adjustment?')
                ->visible(fn ($record) => 
                    $record->needsSecondIpcHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Head')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_SECOND_IPC_HEAD,
                        'approval_ipc_head_id' => auth()->user()->id,
                        'approval_ipc_head_at' => now('Asia/Jakarta')
                    ]);
                    
                    Notification::make()
                        ->title('Request approved successfully by IPC Head (Post Adjustment)')
                        ->success()
                        ->send();
                }),

            Action::make('reject_as_second_ipc_head')
                ->label('Reject as IPC Head (Post Adjustment)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Stock Request')
                ->modalSubheading('Are you sure you want to reject this Stock Request after stock adjustment?')
                ->visible(fn ($record) => 
                    $record->needsSecondIpcHeadApproval() && 
                    $record->isIncrease() && auth()->user()->division?->initial === 'IPC' &&
                    auth()->user()->hasRole('Head')
                )
                ->form([
                    Textarea::make('rejection_reason')
                        ->required()
                        ->maxLength(65535),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_SECOND_IPC_HEAD,
                        'rejection_ipc_head_id' => auth()->user()->id,
                        'rejection_ipc_head_at' => now('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Request rejected successfully by IPC Head (Post Adjustment)')
                        ->warning()
                        ->send();
                }),
            
            Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Stock Request')
                    ->modalSubheading('Are you sure to approve the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsGaAdminApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            'approval_ga_admin_id' => auth()->user()->id,
                            'approval_ga_admin_at' => now('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Request approved successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ga_admin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Stock Request')
                    ->modalSubheading('Are you sure you want to reject this Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsGaAdminApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'GA' &&
                        auth()->user()->hasRole('Admin')
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            'rejection_ga_admin_id' => auth()->user()->id,
                            'rejection_ga_admin_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Approve Stock Request')
                    ->modalSubheading('Are you sure to approve the Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsHcgHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'HCG' &&
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
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HCG_HEAD,
                            'approval_ga_head_id' => auth()->user()->id,
                            'approval_ga_head_at' => now('Asia/Jakarta'),
                            // Automatically mark as delivered
                            'delivered_by' => auth()->user()->id,
                            'delivered_at' => now('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Request approved by HCG Head, stock updated, and marked as delivered successfully')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_hcg_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Stock Request')
                    ->modalSubheading('Are you sure you want to reject this Stock Request?')
                    ->visible(fn ($record) => 
                        $record->needsHcgHeadApproval() && 
                        $record->isIncrease() && auth()->user()->division?->initial === 'HCG' &&
                        auth()->user()->hasRole('Head')
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HCG_HEAD,
                            'rejection_ga_head_id' => auth()->user()->id,
                            'rejection_ga_head_at' => now('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Request rejected successfully')
                            ->warning()
                            ->send();
                    }),
        ];
    }
}
