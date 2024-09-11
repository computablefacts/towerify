<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string hash
 * @property string tag
 * @property int views
 * @property int created_by
 */
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
