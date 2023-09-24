<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use App\Models\Slide;
use App\Models\PressReview;

class Image extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image_url', 'user_id', 'event_id', 'category_id', 'slide_id', 'press_review_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function slide()
    {
        return $this->belongsTo(Slide::class, 'slide_id');
    }

    public function pressReview()
    {
        return $this->belongsTo(PressReview::class, 'press_review_id');
    }
}
