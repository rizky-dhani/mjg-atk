<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource\RelationManagers;
use App\Models\Budget;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Finance';

    public static function canAccess(): bool
    {
        // Only accessible to IPC division
        $user = auth()->user();
        if (!$user || !$user->division) {
            return false;
        }
        
        return $user->division->initial === 'IPC';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->getOptionLabelFromRecordUsing(fn (CompanyDivision $record) => "{$record->name} ({$record->initial})")
                    ->required()
                    ->searchable(['name', 'initial'])
                    ->preload(),
                
                Forms\Components\Select::make('type')
                    ->options([
                        'ATK' => 'ATK (Office Stationery)',
                        'Marketing Media' => 'Marketing Media',
                    ])
                    ->required()
                    ->reactive(),
                
                Forms\Components\TextInput::make('initial_amount')
                    ->label('Initial Budget Amount')
                    ->numeric()
                    ->required()
                    ->prefix('Rp'), // Indonesian Rupiah prefix
                
                Forms\Components\TextInput::make('current_amount')
                    ->label('Current Budget Amount')
                    ->numeric()
                    ->disabled() // This will be calculated automatically
                    ->prefix('Rp'), // Indonesian Rupiah prefix

                Forms\Components\Select::make('effective_year')
                    ->label('Effective Year')
                    ->options(range(date('Y') - 30, date('Y') + 10))
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ATK' => 'info',
                        'Marketing Media' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('initial_amount')
                    ->label('Initial Budget')
                    ->money('IDR')
                    ->formatStateUsing(function ($record) {
                        return 'Rp. '.number_format($record->initial_amount, 0, ',', '.');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Current Budget')
                    ->money('IDR')
                    ->formatStateUsing(function ($record) {
                        return 'Rp. '.number_format($record->current_amount, 0, ',', '.');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('effective_year')
                    ->label('Effective Year')
                    ->date('Y'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modal(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'my-division' => Pages\MyDivisionBudget::route('/my-division'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // For IPC Admin, show all budgets
        $user = auth()->user();
        if ($user && $user->hasRole('Admin') && $user->division && $user->division->initial === 'IPC') {
            return $query;
        }
        
        // For other IPC users, only show their own division
        if ($user && $user->division && $user->division->initial === 'IPC') {
            return $query->where('division_id', $user->division_id);
        }
        
        // For non-IPC users, return empty result
        return $query->whereRaw('1 = 0'); // Always false condition
    }
}
