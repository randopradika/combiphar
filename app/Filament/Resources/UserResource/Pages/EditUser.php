<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Menghapus akun sendiri berarti mengunci diri di luar panel.
            Actions\DeleteAction::make()
                ->hidden(fn () => $this->getRecord()->is(auth()->user())),
        ];
    }

    /** Lihat CreateUser: `role` harus ditulis eksplisit, bukan lewat fill(). */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $data['role'] ?? null;

        $record->fill(Arr::except($data, ['role']));

        // Bidang peran tidak dikirim saat seseorang menyunting dirinya sendiri,
        // jadi ketiadaan nilai berarti "biarkan apa adanya".
        if (filled($role)) {
            $record->forceFill(['role' => $role]);
        }

        $record->save();

        return $record;
    }
}
