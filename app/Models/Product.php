<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'score', 'hash'];

    public static function getScoreOrQueue($name, $description) {

    }
}
