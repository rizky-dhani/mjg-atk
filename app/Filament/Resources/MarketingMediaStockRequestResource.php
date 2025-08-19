<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaStockRequestResource\Pages;
use App\Filament\Resources\MarketingMediaStockRequestResource\RelationManagers;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMedia;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaStockRequestResource extends Resource
{
    protected static ?string $model = MarketingMediaStockRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Stock Requests';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationParentItem = 'Marketing Media';
    protected static ?int $navigationSort = 2;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\Select::make('marketing_media_id')
                            ->label('Marketing Media')
                            ->options(MarketingMedia::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('division_id')
                            ->label('Division')
                            ->options(CompanyDivision::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(), // Disabled because it should match the marketing media's division
                        Forms\Components\Select::make('type')
                            ->options([
                                'increase' => 'Stock Increase',
                                'reduction' => 'Stock Reduction',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->step(1)
                            ->minValue(1),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Stock Information')
                    ->schema([
                        Forms\Components\TextInput::make('previous_stock')
                            ->label('Previous Stock')
                            ->disabled()
                            ->dehydrated(false)
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(1),
                    
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('marketingMedia.name')
                    ->label('Marketing Media')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'increase',
                        'danger' => 'reduction',
                    ])
                    ->labels([
                        'increase' => 'Increase',
                        'reduction' => 'Reduction',
                    ]),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => number_format($state)),
                Tables\Columns\TextColumn::make('previous_stock')
                    ->label('Previous Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => MarketingMediaStockRequest::STATUS_PENDING,
                        'success' => [
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD,
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN,
                            MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD,
                            MarketingMediaStockRequest::STATUS_COMPLETED,
                        ],
                        'danger' => [
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD,
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN,
                            MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD,
                        ],
                    ])
                    ->labels([
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Requested By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('marketing_media_id')
                    ->label('Marketing Media')
                    ->options(MarketingMedia::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(CompanyDivision::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'increase' => 'Stock Increase',
                        'reduction' => 'Stock Reduction',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        MarketingMediaStockRequest::STATUS_PENDING => 'Pending',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_HEAD => 'Approved by Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_HEAD => 'Rejected by Head',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_GA_ADMIN => 'Approved by GA Admin',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_GA_ADMIN => 'Rejected by GA Admin',
                        MarketingMediaStockRequest::STATUS_APPROVED_BY_MKT_HEAD => 'Approved by Marketing Head',
                        MarketingMediaStockRequest::STATUS_REJECTED_BY_MKT_HEAD => 'Rejected by Marketing Head',
                        MarketingMediaStockRequest::STATUS_COMPLETED => 'Completed',
                    ]),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->modal(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaStockRequests::route('/'),
            'view' => Pages\ViewMarketingMediaStockRequest::route('/{record}'),
        ];
    }
}