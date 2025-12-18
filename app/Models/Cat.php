<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    /**
     * 一括代入を許可するカラム
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_path',
        'likes',
    ];
}
