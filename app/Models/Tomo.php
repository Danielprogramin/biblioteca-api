<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tomo extends Model
{
    protected $fillable = ['numero', 'archivo'];

    public function biblioteca()
    {
        return $this->belongsTo(Biblioteca::class);
    }
}
