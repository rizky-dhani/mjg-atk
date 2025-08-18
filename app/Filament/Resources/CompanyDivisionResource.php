<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyDivisionResource\Pages;
use App\Filament\Resources\CompanyDivisionResource\RelationManagers;
use App\Models\CompanyDivision;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyDivisionResource extends Resource
{
    protected static ?string $model = CompanyDivision::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';    
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Divisions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Division Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('initial')
                            ->required()
                            ->maxLength(10)
                            ->helperText('Short code for the division (e.g., IT, HR, FIN)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial')
                    ->label('Initial/Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users Count')
                    ->counts('users')
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                 
            ]);
    }
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Head', 'Admin', 'Admin']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
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
            'index' => Pages\ListCompanyDivisions::route('/'),
        ];
    }
}
