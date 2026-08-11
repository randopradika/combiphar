{{-- Formulirnya didefinisikan di App\Filament\Pages\Footer::form().

     Dibungkus <form> dengan baris aksi milik Filament supaya baris "Simpan"
     yang menempel di bawah layar dan penjaga perubahan-belum-disimpan
     (resources/views/filament/cms-ux.blade.php) ikut berlaku di sini -- keduanya
     memilih .fi-main form .fi-form-actions, bukan halaman per halaman. --}}
<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>
