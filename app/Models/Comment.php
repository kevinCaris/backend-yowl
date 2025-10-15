<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_id',
        'parent_id',
        'content',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    // Commentaire parent (si c'est une réponse)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Réponses à ce commentaire
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Vérifier si c'est un commentaire parent (sur un review)
    public function isParent()
    {
        return is_null($this->parent_id);
    }

    // Vérifier si c'est une réponse à un commentaire
    public function isReply()
    {
        return !is_null($this->parent_id);
    }


    /**
     * Calculate level of recursivity
     * level 1 = main comment(parent_id is null)
     * level 2 = reply level 1
     * level N = reply level N-1
     */
    public function getDepthAttribute()
    {
        $depth = 1;
        $parent = $this->parent; // from model

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
