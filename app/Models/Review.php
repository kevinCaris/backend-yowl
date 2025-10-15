<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'product_link',
        'product_image',
        'content',
        'tag',
        'likes_count',
        'dislikes_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function likeDislikes() {
        return $this->hasMany(LikeDislike::class);
    }


}
