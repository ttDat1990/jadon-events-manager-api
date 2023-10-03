<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

class PressReview extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'author', 'img_url'];
    protected $table = 'press_reviews';

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
