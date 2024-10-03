<?php

namespace App\Modules\CyberBuddy\Http\Requests;

use App\Modules\CyberBuddy\Contracts\Requests\StreamOneFile;
use Illuminate\Foundation\Http\FormRequest;

class StreamOneFileRequest extends FormRequest implements StreamOneFile
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
            //
        ];
    }
}
