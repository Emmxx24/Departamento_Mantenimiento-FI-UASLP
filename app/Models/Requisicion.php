<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisicion extends Model
{
    protected $table = 'Requisicion';

    protected $primaryKey = 'id_requisicion';

    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'proveedor',
        'fecha_req',
        'subtotal',
        'iva',
        'total',
        'cotizacion',
        'es_borrador',
        'id_ticket',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function materiales()
    {
        return $this->hasMany(MaterialRequisicion::class, 'id_requisicion', 'id_requisicion');
    }

    public function poa()
    {
        return $this->hasOne(Poa::class, 'id_requisicion', 'id_requisicion');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin', 'id');
    }
}
