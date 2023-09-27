<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;
use App\Models\User;
use App\Models\AddOn;
use App\Models\Image;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'start_date', 'end_date', 'category_id', 'user_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function add_ons()
    {
        return $this->hasMany(AddOn::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'event_id');
    }
}
