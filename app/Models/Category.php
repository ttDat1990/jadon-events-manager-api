<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\Image;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'name'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function images()
    {
        return $this->hasOne(Image::class, 'category_id');
    }
}
