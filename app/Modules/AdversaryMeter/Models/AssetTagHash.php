<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTagHash extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'assets_tags_hashes';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'hash',
        'views',
        'tag',
        'created_by',
    ];
}
