<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaInventorySettingResource\Pages;
use App\Models\DivisionInventorySetting;
use App\Models\CompanyDivision;
use App\Models\MarketingMediaItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketingMediaInventorySettingResource extends Resource
{
    protected static ?string $model = DivisionInventorySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Stock Limit - Media Cetak';

    protected static ?int $navigationSort = 2;
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Head', 'Admin']) && auth()->user()->division?->initial === 'IPC';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->division?->initial == 'IPC' && auth()->user()->hasRole(['Super Admin', 'Admin']);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inventory Setting')
                    ->schema([
                        Forms\Components\Select::make('division_id')
                            ->label('Division')
                            ->relationship('division', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select a Marketing division')
                            ->options(function () {
                                return CompanyDivision::where('name', 'like', '%Marketing%')
                                    ->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('item_id')
                            ->label('Marketing Media Item')
                            ->relationship('item', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('max_limit')
                            ->label('Max Limit')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Maximum quantity allowed for this item in this division'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Filter to only show Marketing Media items
                $marketingMediaItemIds = MarketingMediaItem::pluck('id');
                $query->whereIn('item_id', $marketingMediaItemIds);
                
                // Filter to only show Marketing divisions
                $marketingDivisionIds = CompanyDivision::where('name', 'like', '%Marketing%')
                    ->pluck('id');
                $query->whereIn('division_id', $marketingDivisionIds);
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_limit')
                    ->label('Max Limit')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division')
                    ->relationship('division', 'name')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return CompanyDivision::where('name', 'like', '%Marketing%')
                            ->pluck('name', 'id');
                    }),
                Tables\Filters\SelectFilter::make('item')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('set_global_limit')
                    ->label('Set Global Limit')
                    ->icon('heroicon-o-globe-alt')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('item_id')
                            ->label('Marketing Media Item')
                            ->relationship('item', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('max_limit')
                            ->label('Max Limit')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('This limit will be applied to all Marketing divisions'),
                    ])
                    ->action(function (array $data) {
                        // Get all Marketing divisions
                        $marketingDivisions = CompanyDivision::where('name', 'like', '%Marketing%')->get();
                        $updatedCount = 0;
                        
                        foreach ($marketingDivisions as $division) {
                            // Update or create division inventory setting for each Marketing division
                            DivisionInventorySetting::updateOrCreate(
                                [
                                    'division_id' => $division->id,
                                    'item_id' => $data['item_id'],
                                ],
                                [
                                    'max_limit' => $data['max_limit'],
                                ]
                            );
                            $updatedCount++;
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Global limits updated')
                            ->body("Successfully set max limit to {$data['max_limit']} for {$updatedCount} Marketing divisions.")
                            ->success()
                            ->send();
                    }),
            ]);
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
            'index' => Pages\ListMarketingMediaInventorySettings::route('/'),
            'create' => Pages\CreateMarketingMediaInventorySetting::route('/create'),
            'edit' => Pages\EditMarketingMediaInventorySetting::route('/{record}/edit'),
        ];
    }
}