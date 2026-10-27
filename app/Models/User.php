<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public $password_para_correo;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
        'job_position_id',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole([
            'super_administrador',
            'administrador_capacitaciones',
            'evaluador',
        ]);
    }

    // Relación con la Agencia
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

   
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
