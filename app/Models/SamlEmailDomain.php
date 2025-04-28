<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SamlEmailDomain extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'saml2_email_domains';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\Slides\Saml2\Models\Tenant::class, 'saml2_tenant_id', 'id');
    }
}
