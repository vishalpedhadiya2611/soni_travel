<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = "car";

    protected $fillable = [
        'name', 'type', 'fair_charge', 'no_of_seat', 'fuel', 'image',
    ];
}
