<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampoFormativo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'user_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('campo_formativos.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && is_null($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criterios()
    {
        return $this->hasMany(Criterio::class);
    }

    public function momentos(): BelongsToMany
    {
        return $this->belongsToMany(Momento::class, 'momento_campo_formativo');
    }
}
