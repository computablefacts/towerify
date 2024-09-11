<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int port_id
 * @property string tag
 */
class PortTag extends Model
{
    use HasFactory;

    protected $table = 'ports_tags';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'port_id',
        'tag',
    ];
}
