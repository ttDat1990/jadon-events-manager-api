<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PressReview extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'author', 'img_url'];
    protected $table = 'press_reviews';
}
