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
 * @property double score
 */
class YnhIoc extends Model
{
    use HasFactory;

    protected $table = 'ynh_iocs';

    protected $fillable = [
        'ynh_server_id',
        'date_min',
        'date_max',
        'score',
    ];

    protected $casts = [
        'date_min' => 'datetime',
        'date_max' => 'datetime',
        'score' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
