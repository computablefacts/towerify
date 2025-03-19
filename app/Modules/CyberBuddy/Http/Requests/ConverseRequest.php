<?php

namespace App\Modules\CyberBuddy\Http\Requests;

use App\Modules\CyberBuddy\Contracts\Requests\Converse;
use Illuminate\Foundation\Http\FormRequest;

class ConverseRequest extends FormRequest implements Converse
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'thread_id' => 'required|string|min:10|max:10|regex:/^[a-zA-Z0-9]+$/',
            'directive' => 'required|string|min:1|max:1024',
        ];
    }
}
