<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int collection_id
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
 * @property int created_by
 */
class File extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_files';

    protected $fillable = [
        'collection_id',
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
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function collection(): HasOne
    {
        return $this->hasOne(Collection::class, 'id', 'collection_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class, 'file_id', 'id');
    }

    public function downloadUrl(): string
    {
        return app_url() . "/cb/web/files/download/{$this->secret}";
    }

    public function streamUrl(): string
    {
        return app_url() . "/cb/web/files/stream/{$this->secret}";
    }
}
