<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTagHash extends Model
{
    use HasFactory;

    protected $table = 'assets_tags_hashes';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'hash',
        'views',
        'tag',
    ];
}
