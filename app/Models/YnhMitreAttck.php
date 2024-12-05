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
 * @property string title
 * @property array tactics
 * @property string description
 */
class YnhMitreAttck extends Model
{
    use HasFactory;

    protected $table = 'ynh_mitre_attck';

    protected $fillable = [
        'uid',
        'title',
        'tactics',
        'description',
    ];

    protected $casts = [
        'tactics' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
