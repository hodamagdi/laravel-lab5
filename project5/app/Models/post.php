<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class post extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'body', 'enabled', 'user_id' ,'image'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
