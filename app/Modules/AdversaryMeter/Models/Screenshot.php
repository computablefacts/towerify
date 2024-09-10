<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    use HasFactory;

    protected $table = 'screenshots';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'port_id',
        'png',
    ];
}
