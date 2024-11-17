<?php

namespace App\Modules\Federa\Http\Requests;

use App\Modules\Federa\Contracts\Requests\UploadOneFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadOneFileRequest extends FormRequest implements UploadOneFile
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->canUseFedera();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'collection' => 'required|string',
            'file' => [
                'required',
                'file',
                'mimes:csv',
                'max:10240',
            ],
        ];
    }
}
