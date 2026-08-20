<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_DISABLED = 'disabled';

    /** Role => label for the CMS. Keep stored values in step with these keys. */
    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_DISABLED => 'Nonaktif',
    ];

    /** Roles that may open the CMS at all. */
    private const PANEL_ROLES = [self::ROLE_ADMIN, self::ROLE_EDITOR];

    /**
     * Required once APP_ENV=production — Filament denies every login without it.
     *
     * This used to `return true`, which meant every row in `users` was a full
     * CMS administrator and the only way to revoke access was to delete the
     * account. It is now an explicit ALLOW-LIST: an unknown or 'disabled' role
     * gets nothing, so access can be revoked (and restored) without destroying
     * the record. `role` is not mass-assignable — set it deliberately.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasCmsRole();
    }

    /**
     * Peran yang boleh membuka CMS — dipakai juga di luar Filament (mis.
     * gerbang pratinjau konten draf), yang tidak punya instance Panel untuk
     * memanggil canAccessPanel(). auth()->check() saja tidak memadai di sana:
     * akun 'disabled' masih terautentikasi sampai sesinya berakhir.
     */
    public function hasCmsRole(): bool
    {
        return in_array($this->role, self::PANEL_ROLES, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
