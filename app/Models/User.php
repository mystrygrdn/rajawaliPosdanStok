<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 'role' TIDAK dimasukkan ke $fillable untuk mencegah mass-assignment vulnerability.
     * Role hanya boleh diset langsung via $user->role = '...' secara eksplisit di seeder/controller.
     */
    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Field login pakai username, bukan email
    public function username(): string
    {
        return 'username';
    }

    // -------------------------------------------------------
    // Helper — dipakai di Blade: @if(auth()->user()->isAdmin())
    // -------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isDapur(): bool
    {
        return $this->role === 'dapur';
    }

    // -------------------------------------------------------
    // Accessor UI — label dan warna badge
    // Pemakaian di Blade: {{ auth()->user()->role_label }}
    // -------------------------------------------------------

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'owner' => 'Owner',
            'dapur' => 'Dapur',
            default => ucfirst($this->role),
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'admin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'owner' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dapur' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }
}