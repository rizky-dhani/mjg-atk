<?php

namespace App\Filament\Resources\MarketingMediaStockRequestResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use App\Models\MarketingMediaStockRequest;
use App\Filament\Resources\MarketingMediaStockRequestResource;

class RequestListMarketingMediaStockRequest extends ListRecords
{
    protected static string $resource = MarketingMediaStockRequestResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?string $navigationLabel = 'Permintaan Media Cetak';
    protected static ?string $modelLabel = 'Permintaan Media Cetak';
    protected static ?string $pluralModelLabel = 'Permintaan Media Cetak';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        
        // Only allow users from Marketing divisions to access this page
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin', 'Head']);
        }
        
        // Also allow IPC, GA, and MKS divisions for approval workflow
        if ($user->division &&
            ($user->division->initial === 'IPC' ||
             $user->division->initial === 'GA' ||
             $user->division->initial === 'MKS')) {
            return $user->hasRole(['Admin', 'Head']);
        }
        
        return false;
    }
    
    public function getBreadcrumb(): string
    {
        return 'Permintaan Media Cetak';
    }
    
    public function getTitle(): string
    {
        return 'Permintaan Media Cetak';
    } 
    
    public function table(Table $table): Table
    {
        $query = MarketingMediaStockRequest::query()
            ->whereIn('status', [
                MarketingMediaStockRequest::STATUS_PENDING,
                MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC,
                MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
            ]);
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('request_number')
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
                    ->colors([
                        'primary' => MarketingMediaStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MarketingMediaStockRequest::STATUS_PENDING => 'warning',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC, MarketingMediaStockRequest::STATUS_APPROVED_BY_IPC_HEAD,  MarketingMediaStockRequest::STATUS_COMPLETED => 'success',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC, MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'danger',
                        default => 'secondary',
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
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                    ])

            ])
            ->actions([
                ViewAction::make(),
                // Approval Actions up to IPC Head
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
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
                            'approval_head_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) =>
                        $record->status === MarketingMediaStockRequest::STATUS_PENDING &&
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                            'rejection_head_id' => auth()->user()->id,
                            'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
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
                            'approval_ipc_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
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
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
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
                            'status' => MarketingMediaStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan Media Cetak berhasil di reject!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}