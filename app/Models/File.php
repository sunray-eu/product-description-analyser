<?php

namespace SunrayEu\ProductDescriptionAnalyser\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['hash', 'name'];

    /**
     * The product messages that belong to the .
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
