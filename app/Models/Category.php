<?php

namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected static string $table = 'categories';
    protected static array $fillable = ['name', 'slug'];
    protected static bool $timestamps = false;
}
