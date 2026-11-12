<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarcaMaterial extends Model
{
    protected $table = 'MarcaMaterial';
    public $timestamps = false;
    protected $fillable = ['id_material', 'id_marca'];
}