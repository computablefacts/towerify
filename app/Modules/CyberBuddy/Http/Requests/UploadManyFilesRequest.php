<?php

namespace App\Modules\CyberBuddy\Http\Requests;

use App\Modules\CyberBuddy\Contracts\Requests\UploadManyFiles;
use App\Modules\CyberBuddy\Rules\IsValidFileType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadManyFilesRequest extends FormRequest implements UploadManyFiles
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->canUseCyberBuddy();
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
            'files.*' => [
                'required',
                'file',
                'max:10240',
                new IsValidFileType()
            ],
        ];
    }
}
