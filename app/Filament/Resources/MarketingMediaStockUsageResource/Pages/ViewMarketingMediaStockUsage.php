<?php

namespace App\Filament\Resources\MarketingMediaStockUsageResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\MarketingMediaStockUsage;
use App\Filament\Resources\MarketingMediaStockUsageResource;

class ViewMarketingMediaStockUsage extends ViewRecord
{
    protected static string $resource = MarketingMediaStockUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->disabled(fn($record) => $record->status !== MarketingMediaStockUsage::STATUS_PENDING),
            
            // Division Head Approval/Rejection Actions
            Actions\Action::make('approve_as_head')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pengeluaran Barang')
                ->modalSubheading('Are you sure to approve this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_PENDING && 
                    Auth::user()->hasRole('Head') &&
                    Auth::user()->division_id === $record->division_id
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD,
                        'approval_head_id' => Auth::user()->id,
                        'approval_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran Barang approved successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_head')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pengeluaran Barang')
                ->modalSubheading('Are you sure to reject this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_PENDING && 
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
                        'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_HEAD,
                        'rejection_head_id' => Auth::user()->id,
                        'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran Barang rejected successfully')
                        ->success()
                        ->send();
                }),

            // GA Admin Approval/Rejection Actions
            Actions\Action::make('approve_as_ga_admin')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pengeluaran Barang')
                ->modalSubheading('Are you sure to approve this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
                        (auth()->user()->hasRole('Admin') && auth()->user()->division && auth()->user()->division->name === 'General Affairs')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                        'approval_ga_admin_id' => Auth::user()->id,
                        'approval_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran Barang approved successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_ga_admin')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pengeluaran Barang')
                ->modalSubheading('Are you sure you want to reject this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_HEAD && 
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
                        'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_GA_ADMIN,
                        'rejection_ga_admin_id' => Auth::user()->id,
                        'rejection_ga_admin_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran Barang rejected successfully')
                        ->success()
                        ->send();
                }),

            // Marketing Support Head Approval/Rejection Actions
            Actions\Action::make('approve_as_mkt_head')
                ->label('Approve & Process Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Approve Pengeluaran Barang')
                ->modalSubheading('Are you sure to approve this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
                    (Auth::user()->hasRole('Head') && Auth::user()->division && Auth::user()->division->name === 'Marketing Support')
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => MarketingMediaStockUsage::STATUS_APPROVED_BY_MKT_HEAD,
                        'approval_marketing_head_id' => Auth::user()->id,
                        'approval_marketing_head_at' => now()->timezone('Asia/Jakarta'),
                    ]);
                    
                    // Process the Pengeluaran Barang
                    $record->processStockUsage();

                    Notification::make()
                        ->title('Pengeluaran Barang approved and stock processed successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject_as_mkt_head')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Reject Pengeluaran Barang')
                ->modalSubheading('Are you sure you want to reject this Pengeluaran Barang?')
                ->visible(fn ($record) => 
                    $record->status === MarketingMediaStockUsage::STATUS_APPROVED_BY_GA_ADMIN && 
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
                        'status' => MarketingMediaStockUsage::STATUS_REJECTED_BY_MKT_HEAD,
                        'rejection_marketing_head_id' => Auth::user()->id,
                        'rejection_marketing_head_at' => now()->timezone('Asia/Jakarta'),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Pengeluaran Barang rejected successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}