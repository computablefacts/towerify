<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property string template
 * @property int created_by
 */
class Prompt extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_prompts';

    protected $fillable = [
        'name',
        'template',
        'created_by',
    ];
}
