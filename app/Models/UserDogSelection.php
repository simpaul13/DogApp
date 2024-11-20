<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDogSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'dog_breed',
    ];
}
