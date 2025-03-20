<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Evaluacion;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'status',
        'role',
        'deactivation_reason',
        'is_confirmed',
    ];

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
            'is_confirmed' => 'boolean',
        ];
    }

    /**
     * Obtener los alumnos del usuario.
     */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    /**
     * Obtener los grupos del usuario.
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }

    /**
     * Obtener los campos formativos del usuario.
     */
    public function camposFormativos(): HasMany
    {
        return $this->hasMany(CampoFormativo::class);
    }

    /**
     * Obtener los criterios del usuario.
     */
    public function criterios(): HasMany
    {
        return $this->hasMany(Criterio::class);
    }

    /**
     * Obtener las evaluaciones del usuario.
     */
    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class);
    }

    /**
     * Check if the user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
