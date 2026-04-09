<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipTemplate extends Model
{
    protected $table = 'slip_templates';
    protected $fillable = ['nama', 'konten', 'is_active'];
}