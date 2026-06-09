<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingData extends Model
{
   
    use HasFactory;

    
    protected $table = 'training_data';

    
    protected $fillable = [
        'father_blood',
        'father_iris',
        'father_hair',
        'father_ear',
        'father_thalassemia',
        'mother_blood',
        'mother_iris',
        'mother_hair',
        'mother_ear',
        'mother_thalassemia',
        'baby_blood',
        'baby_iris',
        'baby_hair',
        'baby_ear',
        'baby_thalassemia_risk',
    ];
}
