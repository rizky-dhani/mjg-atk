<?php

namespace App\Filament\Resources\OfficeStationeryStockRequestResource\Pages;

use App\Helpers\RequestStatusChecker;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use App\Models\OfficeStationeryStockRequest;
use App\Filament\Resources\OfficeStationeryStockRequestResource;
use App\Helpers\UserRoleChecker;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestListOfficeStationeryStockRequest extends ListRecords
{
    protected static string $resource = OfficeStationeryStockRequestResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Permintaan ATK';
    protected static ?string $modelLabel = 'Permintaan ATK';
    protected static ?string $pluralModelLabel = 'Permintaan ATK';
    public function getBreadcrumb(): string
    {
        return 'Permintaan ATK';
    }
    
    public function getTitle(): string
    {
        return 'Permintaan ATK';
    } 
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                if(UserRoleChecker::isIpcAdmin()){
                    $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD)->orderByDesc('created_at');
                }elseif(UserRoleChecker::isIpcHead()){
                    $query->where('status', OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC)->orderByDesc('created_at');
                }elseif(UserRoleChecker::isDivisionHead()){
                    $query->orderByDesc('created_at')->orderByDesc('request_number');
                }
            })
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
                        'primary' => OfficeStationeryStockRequest::TYPE_INCREASE,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::TYPE_INCREASE => 'Increase',
                    }),
                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockRequest::STATUS_PENDING => 'warning',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC, OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,  OfficeStationeryStockRequest::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC, OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'danger',
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
                        OfficeStationeryStockRequest::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC => 'Approved by IPC',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC => 'Rejected by IPC',
                        OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD => 'Approved by IPC Head',
                        OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD => 'Rejected by IPC Head',
                        OfficeStationeryStockRequest::STATUS_COMPLETED => 'Completed',
                    ])

            ])
            ->actions([
                ViewAction::make(),
                // Approval Actions up to IPC Head
                // Action::make('approve_as_head')
                //     ->label('Approve')
                //     ->icon('heroicon-o-check-circle')
                //     ->color('success')
                //     ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                //     ->requiresConfirmation()
                //     ->action(function ($record) {
                //         $record->update([
                //             'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_HEAD,
                //             'approval_head_id' => auth()->user()->id,
                //             'approval_head_at' => now()->timezone('Asia/Jakarta'),
                //         ]);
                        
                //         Notification::make()
                //             ->title('Pemasukan ATK berhasil di-approve!')
                //             ->success()
                //             ->send();
                //     }),
                
                // Action::make('reject_as_head')
                //     ->label('Reject')
                //     ->icon('heroicon-o-x-circle')
                //     ->color('danger')
                //     ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromDivisionHead($record))
                //     ->form([
                //         Textarea::make('rejection_reason')
                //             ->required()
                //             ->maxLength(65535),
                //     ])
                //     ->action(function ($record, array $data) {
                //         $record->update([
                //             'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_HEAD,
                //             'rejection_head_id' => auth()->user()->id,
                //             'rejection_head_at' => now()->timezone('Asia/Jakarta'),
                //             'rejection_reason' => $data['rejection_reason'],
                //         ]);
                        
                //         Notification::make()
                //             ->title('Pemasukan ATK berhasil di-reject!')
                //             ->warning()
                //             ->send();
                //     }),
                
                Action::make('approve_as_ipc')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcAdmin($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC,
                            'approval_ipc_id' => auth()->user()->id,
                            'approval_ipc_at' => now()->timezone('Asia/Jakarta'),
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcAdmin($record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC,
                            'rejection_ipc_id' => auth()->user()->id,
                            'rejection_ipc_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ipc_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcHead($record))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_APPROVED_BY_IPC_HEAD,
                            'approval_ipc_head_id' => auth()->user()->id,
                            'approval_ipc_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-approve!')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject_as_ipc_head')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => RequestStatusChecker::atkStockRequestNeedApprovalFromIpcHead($record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->required()
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => OfficeStationeryStockRequest::STATUS_REJECTED_BY_IPC_HEAD,
                            'rejection_ipc_head_id' => auth()->user()->id,
                            'rejection_ipc_head_at' => now()->timezone('Asia/Jakarta'),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pemasukan ATK berhasil di-reject!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
