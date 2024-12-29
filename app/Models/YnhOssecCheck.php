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
 * @property int ynh_ossec_policy_id
 * @property int uid
 * @property string title
 * @property string description
 * @property string rationale
 * @property string impact
 * @property string remediation
 * @property array references
 * @property array compliance
 * @property array requirements
 */
class YnhOssecCheck extends Model
{
    use HasFactory;

    protected $table = 'ynh_ossec_checks';

    protected $fillable = [
        'ynh_ossec_policy_id',
        'uid',
        'title',
        'description',
        'rationale',
        'impact',
        'remediation',
        'references',
        'compliance',
        'requirements',
    ];

    protected $casts = [
        'uid' => 'integer',
        'references' => 'array',
        'compliance' => 'array',
        'requirements' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(YnhOssecPolicy::class, 'ynh_ossec_policy_id', 'id');
    }
}
