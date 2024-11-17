<?php

namespace App\Modules\Federa\Models;

use App\Modules\Federa\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string name_normalized
 * @property string extension
 * @property string path
 * @property int size
 * @property string md5
 * @property string sha1
 * @property string mime_type
 * @property string secret
 * @property boolean is_deleted
 * @property int collection_id
 * @property boolean has_headers
 * @property ?string column_mapping
 * @property int created_by
 */
class CsvFile extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'f_csv_files';

    protected $fillable = [
        'name',
        'name_normalized',
        'extension',
        'path',
        'size',
        'md5',
        'sha1',
        'mime_type',
        'secret',
        'is_deleted',
        'collection_id',
        'has_headers',
        'column_mapping',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'has_headers' => 'boolean',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function collection(): HasOne
    {
        return $this->hasOne(Collection::class, 'id', 'collection_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(CsvRow::class, 'csv_file_id', 'id');
    }

    public function downloadUrl(): string
    {
        return app_url() . "/f/web/files/download/{$this->secret}";
    }

    public function streamUrl(): string
    {
        return app_url() . "/f/web/files/stream/{$this->secret}";
    }
}
