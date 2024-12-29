<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string uid
 * @property string name
 * @property string description
 * @property array references
 * @property array requirements
 */
class YnhOssecPolicy extends Model
{
    use HasFactory;

    protected $table = 'ynh_ossec_policies';

    protected $fillable = [
        'uid',
        'name',
        'description',
        'references',
        'requirements',
    ];

    protected $casts = [
        'references' => 'array',
        'requirements' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
