<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeStationeryCategoryResource\Pages;
use App\Filament\Resources\OfficeStationeryCategoryResource\RelationManagers;
use App\Helpers\UserRoleChecker;
use App\Models\OfficeStationeryCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfficeStationeryCategoryResource extends Resource
{
    protected static ?string $model = OfficeStationeryCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $navigationGroup = 'Alat Tulis Kantor';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                ->visible(fn() => UserRoleChecker::isIpcAdmin()),
                Tables\Actions\DeleteAction::make()
                ->visible(fn() => UserRoleChecker::isIpcAdmin()),
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
        $authenticatedUsers = UserRoleChecker::isInDivisionWithInitial('IPC') && UserRoleChecker::isDivisionAdmin() || UserRoleChecker::isInDivisionWithInitial('GA') && UserRoleChecker::isDivisionAdmin();
        return $authenticatedUsers;
    }

    public static function canCreate(): bool
    {
        $authenticatedUsers = UserRoleChecker::isInDivisionWithInitial('IPC') && UserRoleChecker::isDivisionAdmin() || UserRoleChecker::isInDivisionWithInitial('GA') && UserRoleChecker::isDivisionAdmin();
        return $authenticatedUsers;
    }

    public static function canEdit($record): bool
    {
        $authenticatedUsers = UserRoleChecker::isInDivisionWithInitial('IPC') && UserRoleChecker::isDivisionAdmin() || UserRoleChecker::isInDivisionWithInitial('GA') && UserRoleChecker::isDivisionAdmin();
        return $authenticatedUsers;
    }

    public static function canDelete($record): bool
    {
        $authenticatedUsers = UserRoleChecker::isInDivisionWithInitial('IPC') && UserRoleChecker::isDivisionAdmin() || UserRoleChecker::isInDivisionWithInitial('GA') && UserRoleChecker::isDivisionAdmin();
        return $authenticatedUsers;
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
            'index' => Pages\ListOfficeStationeryCategories::route('/'),
        ];
    }
}
