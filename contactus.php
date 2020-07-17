<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class contactus extends Model
{
    protected $table = "contactuses";

    protected $fillable = [
        'name', 'email' , 'phone', 'message',
    ];
}
