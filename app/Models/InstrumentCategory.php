<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumentCategory extends Model
{
    protected $fillable = ['instrument_id', 'name'];
    protected $hidden = ['pivot'];

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }
}
