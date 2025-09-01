<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'slug' => ['sometimes'],
            'description' => ['required'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'registration_opens_at' => ['required', 'date', 'before:starts_at'],
            'registration_closes_at' => ['required', 'date', 'after:registration_opens_at', 'before_or_equal:ends_at'],
            'banner' => ['nullable', File::image()->max('6mb')],
        ];
    }
}
