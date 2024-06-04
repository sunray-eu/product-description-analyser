<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'score', 'hash'];

    public static function getScoreOrQueue($name, $description) {

    }

    /**
     * The product messages that belong to the file.
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class);
    }

}
