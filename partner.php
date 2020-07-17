<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class partner extends Model
{
    protected $table = "partner";

    protected $fillable = [
        'image',
    ];
}
