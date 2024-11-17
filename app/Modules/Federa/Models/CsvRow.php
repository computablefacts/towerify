<?php

namespace App\Modules\Federa\Models;

use App\Modules\Federa\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int collection_id
 * @property string contents
 * @property int csv_upload_id
 * @property boolean is_deleted
 * @property ?Carbon imported_at
 * @property ?Carbon warned_at
 * @property ?Carbon failed_at
 * @property int created_by
 */
class CsvRow extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'f_csv_rows';

    protected $fillable = [
        'collection_id',
        'contents',
        'csv_file_id',
        'is_deleted',
        'imported_at',
        'warned_at',
        'failed_at',
        'created_by',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'imported_at' => 'datetime',
        'warned_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function collection(): HasOne
    {
        return $this->hasOne(Collection::class, 'id', 'collection_id');
    }

    public function file(): HasOne
    {
        return $this->hasOne(CsvFile::class, 'id', 'csv_file_id');
    }
}
