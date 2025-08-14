<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrintMediaStockMovementResource\Pages;
use App\Filament\Resources\PrintMediaStockMovementResource\RelationManagers;
use App\Models\PrintMediaStockMovement;
use App\Models\PrintMedia;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrintMediaStockMovementResource extends Resource
{
    protected static ?string $model = PrintMediaStockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Stock Movements';
    protected static ?string $navigationGroup = 'Stocks';
    protected static ?string $navigationParentItem = 'Print Media';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Movement Information')
                    ->schema([
                        Forms\Components\Select::make('print_media_id')
                            ->label('Print Media')
                            ->options(PrintMedia::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('division_id')
                            ->label('Division')
                            ->options(CompanyDivision::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(), // Disabled because it should match the print media's division
                        Forms\Components\Select::make('movement_type')
                            ->options([
                                'in' => 'Stock In',
                                'out' => 'Stock Out',
                                'transfer' => 'Transfer',
                                'adjustment' => 'Adjustment',
                                'damaged' => 'Damaged',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('movement_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Quantity & Reference')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->step(1)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('previous_stock')
                            ->label('Previous Stock')
                            ->disabled()
                            ->dehydrated(false)
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
                    
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
                Tables\Columns\TextColumn::make('printMedia.name')
                    ->label('Print Media')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('movement_type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                        'warning' => ['transfer', 'adjustment'],
                        'gray' => ['damaged', 'expired'],
                    ]),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state)),
                Tables\Columns\TextColumn::make('previous_stock')
                    ->label('Previous Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('movement_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('print_media_id')
                    ->label('Print Media')
                    ->options(PrintMedia::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(CompanyDivision::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('movement_type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Adjustment (use negative quantity to reduce stock)',
                        'damaged' => 'Damaged',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\Filter::make('movement_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date),
                            );
                    }),
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
            ->defaultSort('movement_date', 'desc');
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
            'index' => Pages\ListPrintMediaStockMovements::route('/'),
            'view' => Pages\ViewPrintMediaStockMovement::route('/{record}'),
        ];
    }
}