<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use App\Models\MarketingMediaStockRequest;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\MarketingMediaStockRequestResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewMarketingMediaStockRequest extends ViewRecord
{
    protected static string $resource = MarketingMediaStockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->modal(),
            // Division Head Approval Actions
            Action::make('approve_by_head')
                ->label('Approve')
                ->modalHeading('Approve Stock Request')
                ->modalSubheading('Are you sure you want to approve this stock request?')
                ->modalSubmitActionLabel('Approve')
                ->action(function (MarketingMediaStockRequest $record) {
                    $record->status = MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD;
                    $record->approval_head_id = Auth::id();
                    $record->approval_head_at = now();
                    $record->save();
                    
                    Notification::make()
                        ->title('Stock Request Approved')
                        ->body('The stock request has been approved.')
                        ->success()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsDivisionHeadApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.head')
                )
                ->requiresConfirmation(),
                
            Action::make('reject_by_head')
                ->label('Reject')
                ->modalHeading('Reject Stock Request')
                ->modalSubheading('Are you sure you want to reject this stock request?')
                ->modalSubmitActionLabel('Reject')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(1000)
                ])
                ->action(function (MarketingMediaStockRequest $record, array $data) {
                    $record->status = MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD;
                    $record->rejection_head_id = Auth::id();
                    $record->rejection_head_at = now();
                    $record->rejection_reason = $data['rejection_reason'];
                    $record->save();
                    
                    Notification::make()
                        ->title('Stock Request Rejected')
                        ->body('The stock request has been rejected.')
                        ->danger()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsDivisionHeadApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.head')
                )
                ->requiresConfirmation()
                ->color('danger'),
                
            // GA Admin Approval Actions
            Action::make('approve_by_ga_admin')
                ->label('Approve')
                ->modalHeading('Approve Stock Request')
                ->modalSubheading('Are you sure you want to approve this stock request?')
                ->modalSubmitActionLabel('Approve')
                ->action(function (MarketingMediaStockRequest $record) {
                    $record->status = MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN;
                    $record->approval_admin_ga_id = Auth::id();
                    $record->approval_admin_ga_at = now();
                    $record->save();
                    
                    Notification::make()
                        ->title('Stock Request Approved')
                        ->body('The stock request has been approved.')
                        ->success()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsGaAdminApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.ga.admin')
                )
                ->requiresConfirmation(),
                
            Action::make('reject_by_ga_admin')
                ->label('Reject')
                ->modalHeading('Reject Stock Request')
                ->modalSubheading('Are you sure you want to reject this stock request?')
                ->modalSubmitActionLabel('Reject')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(1000)
                ])
                ->action(function (MarketingMediaStockRequest $record, array $data) {
                    $record->status = MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN;
                    $record->rejection_admin_ga_id = Auth::id();
                    $record->rejection_admin_ga_at = now();
                    $record->rejection_reason = $data['rejection_reason'];
                    $record->save();
                    
                    Notification::make()
                        ->title('Stock Request Rejected')
                        ->body('The stock request has been rejected.')
                        ->danger()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsGaAdminApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.ga.admin')
                )
                ->requiresConfirmation()
                ->color('danger'),
                
            // Marketing Support Head Approval Actions
            Action::make('approve_by_mkt_head')
                ->label('Approve & Reduce Stock')
                ->modalHeading('Approve Stock Request')
                ->modalSubheading('Are you sure you want to approve this stock request? This will reduce the stock.')
                ->modalSubmitActionLabel('Approve & Reduce Stock')
                ->action(function (MarketingMediaStockRequest $record) {
                    $record->status = MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD;
                    $record->approval_mkt_head_id = Auth::id();
                    $record->approval_mkt_head_at = now();
                    $record->save();
                    
                    // Process stock reduction
                    $record->processStockReduction();
                    
                    Notification::make()
                        ->title('Stock Request Approved')
                        ->body('The stock request has been approved and stock has been reduced.')
                        ->success()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsMarketingSupportHeadApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.mkt.head')
                )
                ->requiresConfirmation(),
                
            Action::make('reject_by_mkt_head')
                ->label('Reject')
                ->modalHeading('Reject Stock Request')
                ->modalSubheading('Are you sure you want to reject this stock request?')
                ->modalSubmitActionLabel('Reject')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(1000)
                ])
                ->action(function (MarketingMediaStockRequest $record, array $data) {
                    $record->status = MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD;
                    $record->rejection_mkt_head_id = Auth::id();
                    $record->rejection_mkt_head_at = now();
                    $record->rejection_reason = $data['rejection_reason'];
                    $record->save();
                    
                    Notification::make()
                        ->title('Stock Request Rejected')
                        ->body('The stock request has been rejected.')
                        ->danger()
                        ->send();
                })
                ->visible(fn (MarketingMediaStockRequest $record) => 
                    $record->needsMarketingSupportHeadApproval() && 
                    auth()->user()->can('approve.marketing.media.stock.request.mkt.head')
                )
                ->requiresConfirmation()
                ->color('danger'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Request Information')
                    ->schema([
                        TextEntry::make('marketingMedia.name')
                            ->label('Marketing Media'),
                        TextEntry::make('division.name')
                            ->label('Division'),
                        TextEntry::make('type')
                            ->label('Request Type')
                            ->formatStateUsing(fn (string $state): string => 
                                $state === 'increase' ? 'Stock Increase' : 'Stock Reduction'
                            ),
                    ])
                    ->columns(2),
                    
                \Filament\Infolists\Components\Section::make('Quantity & Stock')
                    ->schema([
                        TextEntry::make('quantity')
                            ->formatStateUsing(fn (int $state): string => number_format($state)),
                        TextEntry::make('previous_stock')
                            ->label('Previous Stock'),
                    ])
                    ->columns(2),
                    
                \Filament\Infolists\Components\Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'success',
                                MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                                MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Head',
                                MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Head',
                                MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                                default => ucfirst($state),
                            }),
                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                in_array($record->status, [
                                    MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                                    MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                                    MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                                ])
                            ),
                    ])
                    ->columns(1),
                    
                \Filament\Infolists\Components\Section::make('Approval History')
                    ->schema([
                        TextEntry::make('divisionHead.name')
                            ->label('Division Head')
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_head_id || $record->rejection_head_id
                            ),
                        TextEntry::make('approval_head_at')
                            ->label('Approved At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_head_id
                            ),
                        TextEntry::make('rejection_head_at')
                            ->label('Rejected At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->rejection_head_id
                            ),
                            
                        TextEntry::make('gaAdmin.name')
                            ->label('GA Admin')
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_admin_ga_id || $record->rejection_admin_ga_id
                            ),
                        TextEntry::make('approval_admin_ga_at')
                            ->label('Approved At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_admin_ga_id
                            ),
                        TextEntry::make('rejection_admin_ga_at')
                            ->label('Rejected At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->rejection_admin_ga_id
                            ),
                            
                        TextEntry::make('marketingSupportHead.name')
                            ->label('Marketing Support Head')
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_mkt_head_id || $record->rejection_mkt_head_id
                            ),
                        TextEntry::make('approval_mkt_head_at')
                            ->label('Approved At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->approval_mkt_head_id
                            ),
                        TextEntry::make('rejection_mkt_head_at')
                            ->label('Rejected At')
                            ->dateTime()
                            ->visible(fn (MarketingMediaStockRequest $record): bool => 
                                $record->rejection_mkt_head_id
                            ),
                    ])
                    ->columns(3),
                    
                \Filament\Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('notes'),
                        TextEntry::make('creator.name')
                            ->label('Requested By'),
                        TextEntry::make('created_at')
                            ->label('Request Date')
                            ->dateTime(),
                    ])
                    ->columns(1),
            ]);
    }
}
