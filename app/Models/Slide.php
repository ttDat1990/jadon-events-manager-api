<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Image;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content'];

    public function images()
    {
        return $this->hasOne(Image::class, 'slide_id');
    }
}
