<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\OfficeStationeryStockUsage;
use App\Filament\Resources\OfficeStationeryStockUsageResource;

class ViewOfficeStationeryStockUsage extends ViewRecord
{
    protected static string $resource = OfficeStationeryStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Division Head Approval/Rejection Actions
            Actions\Action::make('approve_as_head')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_PENDING && 
                    Auth::user()->hasRole('Head') &&
                    Auth::user()->division_id === $record->division_id
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                        'approval_head_id' => Auth::user()->id,
                        'approval_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Stock Usage approved successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_head')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_PENDING && 
                    Auth::user()->hasRole('Head') &&
                    Auth::user()->division_id === $record->division_id
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
                        'rejection_head_id' => Auth::user()->id,
                        'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Stock Usage rejected successfully')
                        ->success()
                        ->send();
                }),

            // GA Admin Approval/Rejection Actions
            Actions\Action::make('approve_as_ga_admin')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        'approval_ga_admin_id' => Auth::user()->id,
                        'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Stock Usage approved successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_ga_admin')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
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
                        'rejection_ga_admin_id' => Auth::user()->id,
                        'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Stock Usage rejected successfully')
                        ->success()
                        ->send();
                }),

            // Supervisor/Head Marketing Approval/Rejection Actions
            Actions\Action::make('approve_as_supervisor_marketing')
                ->label('Approve & Process Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                    (Auth::user()->hasRole('Head') && Auth::user()->division && Auth::user()->division->name === 'Marketing Support')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_SUPERVISOR_MARKETING,
                        'approval_mkt_head_id' => Auth::user()->id,
                        'approval_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    // Process the stock usage
                    $record->processStockUsage();
                    
                    Notification::make()
                        ->title('Stock Usage approved and stock processed successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_supervisor_marketing')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => 
                    $record->status === OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                    (Auth::user()->hasRole('Head') && Auth::user()->division && Auth::user()->division->name === 'Marketing Support')
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
                        'status' => OfficeStationeryStockUsage::STATUS_REJECTED_BY_SUPERVISOR_MARKETING,
                        'rejection_mkt_head_id' => Auth::user()->id,
                        'rejection_mkt_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Stock Usage rejected successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
