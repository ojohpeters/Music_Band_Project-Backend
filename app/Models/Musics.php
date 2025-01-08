<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Musics extends Model
{
    /** @use HasFactory<\Database\Factories\MusicsFactory> */
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'album',
        'artist',
        'price',
        'is_free',
        'file_path',
        'cover_image'
    ];
}
