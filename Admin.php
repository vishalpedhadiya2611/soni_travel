<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
	protected $table = "soni_admin";

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

}
