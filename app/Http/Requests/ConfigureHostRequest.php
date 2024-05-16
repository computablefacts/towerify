<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConfigureHostRequest extends FormRequest implements \App\Contracts\Requests\ConfigureHost
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->canManageServers();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:30',
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|min:1|max:20',
            'domain' => 'required|string|min:1|max:100',
        ];
    }
}
