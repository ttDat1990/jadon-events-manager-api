<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;

class AddOn extends Model
{
    use HasFactory;

    protected $fillable = ['department', 'responsible', 'event_id'];
    protected $table = 'add_ons';
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
