<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'email',
        'puesto',
        'fecha_nacimiento', 
        'domicilio', 
        'latitud', 
        'longitud',
    ];

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

}
