<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTag extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'assets_tags';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'asset_id',
        'tag',
        'created_by',
    ];
}
