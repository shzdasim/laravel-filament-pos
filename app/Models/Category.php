<?php

namespace App\Models;

use App\Exceptions\CategoryDeletionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            if ($category->products()->exists()) {
                throw new CategoryDeletionException();
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
