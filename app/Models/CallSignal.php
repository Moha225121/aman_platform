<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallSignal extends Model
{
    public $timestamps = false;

    protected $fillable = ['booking_id', 'sender_id', 'type', 'payload', 'created_at'];

    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];
}
