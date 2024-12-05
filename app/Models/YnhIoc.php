<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int ynh_server_id
 * @property Carbon date_min
 * @property Carbon date_max
 * @property array iocs
 * @property integer count
 * @property double min
 * @property double max
 * @property double sum
 * @property double mean
 * @property double median
 * @property double std_dev
 * @property double variance
 * @property boolean is_anomaly
 */
class YnhIoc extends Model
{
    use HasFactory;

    protected $table = 'ynh_iocs';

    protected $fillable = [
        'ynh_server_id',
        'date_min',
        'date_max',
        'iocs',
        'count',
        'min',
        'max',
        'sum',
        'mean',
        'median',
        'std_dev',
        'variance',
        'is_anomaly',
    ];

    protected $casts = [
        'date_min' => 'datetime',
        'date_max' => 'datetime',
        'iocs' => 'array',
        'count' => 'integer',
        'min' => 'float',
        'max' => 'float',
        'sum' => 'float',
        'mean' => 'float',
        'median' => 'float',
        'std_dev' => 'float',
        'variance' => 'float',
        'is_anomaly' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
