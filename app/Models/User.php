<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
        'job_position_id',
        'is_active',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
       // Solo permitimos el acceso si la cuenta está activa. esta funcioin se ve en produccion
        return $this->is_active;
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
            'password' => 'hashed',
        ];
    }
}
