<?php

namespace App\Filament\Resources\OfficeStationeryStockUsageResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use App\Models\OfficeStationeryStockUsage;
use App\Filament\Resources\OfficeStationeryStockUsageResource;

class UsageListOfficeStationeryStockUsage extends ListRecords
{
    protected static string $resource = OfficeStationeryStockUsageResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?string $navigationLabel = 'Pengeluaran ATK';
    protected static ?string $modelLabel = 'Pengeluaran ATK';
    protected static ?string $pluralModelLabel = 'Pengeluaran ATK';
    
    public function getBreadcrumb(): string
    {
        return 'Pengeluaran ATK';
    }
    
    public function getTitle(): string
    {
        return 'Pengeluaran ATK';
    } 
    
    public function table(Table $table): Table
    {            
        $query = OfficeStationeryStockUsage::query()
            ->whereIn('status', [
                OfficeStationeryStockUsage::STATUS_PENDING,
                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD,
                OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN,
                OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD,
            ]);

        return $table
            ->modifyQueryUsing(fn() => $query->orderByDesc('usage_number')->orderByDesc('created_at'))
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
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'Pending',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD => 'Approved by HCG Head',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'Rejected by HCG Head',
                        OfficeStationeryStockUsage::STATUS_COMPLETED => 'Completed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OfficeStationeryStockUsage::STATUS_PENDING => 'warning',
                        OfficeStationeryStockUsage::STATUS_APPROVED_BY_HEAD, OfficeStationeryStockUsage::STATUS_APPROVED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD, OfficeStationeryStockUsage::STATUS_COMPLETED => 'success',
                        OfficeStationeryStockUsage::STATUS_REJECTED_BY_HEAD, OfficeStationeryStockUsage::STATUS_REJECTED_BY_GA_ADMIN, OfficeStationeryStockUsage::STATUS_REJECTED_BY_HCG_HEAD => 'danger',
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
                    ])
            ])
            ->actions([
                ViewAction::make(),
                // Approval Actions for Usage
                Action::make('approve_as_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->canBeApprovedByDivisionHead())
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
                    ->visible(fn ($record) => $record->canBeApprovedByDivisionHead())
                    ->form([
                        Textarea::make('rejection_reason')
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
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_ga_admin')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->canBeApprovedByGaAdmin())
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
                    ->visible(fn ($record) => $record->canBeApprovedByGaAdmin())
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
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
                            ->warning()
                            ->send();
                    }),
                
                Action::make('approve_as_hcg_head')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->canBeApprovedByHcgHead())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => OfficeStationeryStockUsage::STATUS_APPROVED_BY_HCG_HEAD,
                            'approval_hcg_head_id' => auth()->user()->id,
                            'approval_hcg_head_at' => now()->timezone('Asia/Jakarta')
                        ]);
                        
                        // Process the stock usage
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
                    ->visible(fn ($record) => $record->canBeApprovedByHcgHead())
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
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
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
