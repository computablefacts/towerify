<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * This trait scopes the actions available to a given user using the `user_id` field of a model.
 */
trait IsTenantAware2
{
    public function deleting(Model $model)
    {
        $user = Auth::user();

        if (!$user || !$user->tenant_id || $user->tenant_id === optional($model->tenant())->id) {
            return true;
        }
        return false;
    }

    public function updating(Model $model)
    {
        $user = Auth::user();

        if (!$model->user_id) {
            $model->user_id = $user?->id;
            return true;
        }
        if (!$user || !$user->tenant_id || $user->tenant_id === optional($model->tenant())->id) {
            return true;
        }
        return false;
    }

    public function creating(Model $model)
    {
        $user = Auth::user();

        if (!$model->user_id) {
            $model->user_id = $user?->id;
            return true;
        }
        if (!$user || !$user->tenant_id || $user->tenant_id === optional($model->tenant())->id) {
            return true;
        }
        return false;
    }
}