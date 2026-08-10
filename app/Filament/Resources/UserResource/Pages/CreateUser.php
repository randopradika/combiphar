<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * `role` sengaja tidak mass-assignable (App\Models\User), sehingga create()
     * akan MEMBUANGNYA tanpa peringatan dan setiap akun baru jatuh ke nilai
     * bawaan kolom. Tulis secara eksplisit lewat forceFill.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $role = $data['role'] ?? null;

        $user = static::getModel()::create(Arr::except($data, ['role']));

        if (filled($role)) {
            $user->forceFill(['role' => $role])->save();
        }

        return $user;
    }
}
