<?php

namespace App\Http\Requests;

use App\Enums\DownloadCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDownloadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isSenior();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(DownloadCategory::class)],
            'description' => ['nullable', 'string', 'max:1024'],
            'file' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt', 'max:20480'],
        ];
    }
}
