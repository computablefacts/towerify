<?php

namespace App\Modules\CyberBuddy\Models;

use App\Hashing\TwHasher;
use App\Modules\CyberBuddy\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string description
 * @property string query
 * @property boolean copied
 * @property boolean deduplicated
 * @property boolean updatable
 * @property array credentials
 * @property array schema
 * @property int nb_rows
 * @property string last_error
 * @property int created_by
 * @property Carbon started_at
 * @property Carbon finished_at
 */
class Table extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_tables';

    protected $fillable = [
        'name',
        'description',
        'copied',
        'deduplicated',
        'created_by',
        'last_error',
        'started_at',
        'finished_at',
        'updatable',
        'schema',
        'credentials',
        'nb_rows',
        'query',
    ];

    protected $casts = [
        'copied' => 'boolean',
        'deduplicated' => 'boolean',
        'updatable' => 'boolean',
        'schema' => 'array',
        'nb_rows' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    protected function credentials(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode(TwHasher::unhash($value), true),
            set: fn(array $value) => TwHasher::hash(json_encode($value))
        );
    }

    public function status(): string
    {
        if ($this->last_error) {
            return 'Error: ' . $this->last_error;
        }
        if ($this->started_at && !$this->finished_at) {
            return 'Importing...';
        }
        if ($this->started_at && $this->finished_at) {
            return 'Imported';
        }
        return '';
    }
}
