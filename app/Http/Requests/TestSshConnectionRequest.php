<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TestSshConnectionRequest extends FormRequest implements \App\Contracts\Requests\TestSshConnection
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->canListServers();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
        ];
    }
}
