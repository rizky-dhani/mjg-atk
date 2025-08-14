<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Hash;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New User')
                ->mutateFormDataUsing( function(array $data){
                    $data['password'] = Hash::make('Atk2025!');
                    return $data;
                })
                ->successNotificationTitle('User created successfully!'),
        ];
    }
}
