<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PressReview;
use App\Models\Like;


class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pressReview()
    {
        return $this->belongsTo(PressReview::class, 'press_id');
    }

    public function likesCount()
    {
        return $this->hasMany(Like::class)->count();
    }

}
