<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaHistory extends Model
{
    use HasFactory;

    protected $table = '_tb_demanda_status_history';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'demanda_id',
        'status',
        'changed_by'
    ];

    public function user()
    {
        // 🔹 changed_by (FK) → id_user (PK de _tb_usuarios)
        return $this->belongsTo(User::class, 'changed_by', 'id_user');
    }
}
