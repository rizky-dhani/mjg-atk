<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingMediaItemResource\Pages;
use App\Filament\Resources\MarketingMediaItemResource\RelationManagers;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingMediaItemResource extends Resource
{
    protected static ?string $model = MarketingMediaItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'List Item';
    protected static ?string $navigationGroup = 'Media Cetak';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\Select::make('marketing_media_category_id')
                            ->label('Category')
                            ->relationship('category', 'name', 
                                function (Builder $query) {
                                    return $query->orderBy('id');
                                })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('unit_of_measure')
                            ->label('Unit of Measure')
                            ->required()
                            ->maxLength(10)
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('marketing_media_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
        $user = auth()->user();
        if ($user->hasRole(['Super Admin'])) {
            return true;
        }
        
        // Check if user's division name contains 'Marketing' and from IPC division
        if ($user->division && strpos($user->division->name, 'Marketing') !== false || $user->division?->initial === 'IPC') {
            return $user->hasRole(['Admin', 'Head']);
        }
        
        // Hide from users who don't belong to any Marketing divisions
        return false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Admin']) && $user->division->initial === 'IPC') {
            return true;
        }
        
        // Check if user's division name contains 'Marketing'
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin']);
        }
        
        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Admin']) && $user->division->initial === 'IPC') {
            return true;
        }
        
        // Check if user's division name contains 'Marketing'
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin']);
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if ($user->hasRole(['Super Admin', 'Admin']) && $user->division->initial === 'IPC') {
            return true;
        }
        
        // Check if user's division name contains 'Marketing'
        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
            return $user->hasRole(['Admin']);
        }
        
        return false;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MarketingMediaItemPricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingMediaItems::route('/'),
        ];
    }
}
