<?php

namespace App\Models;

class Flight extends BaseModel
{
    protected $guarded = ['id'];

    protected $dates = ['departure_time', 'arrival_time'];
}
