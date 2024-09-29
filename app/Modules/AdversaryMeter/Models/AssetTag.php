<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int asset_id
 * @property string tag
 * @property int created_by
 */
class AssetTag extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'am_assets_tags';

    protected $fillable = [
        'asset_id',
        'tag',
        'created_by',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
