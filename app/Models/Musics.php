<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

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
        'cover_image',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($music) {
            // Check if the file exists
            if (File::exists(public_path($music->file_path))) {
                File::delete(public_path($music->file_path));
            }
        });
    }
}
