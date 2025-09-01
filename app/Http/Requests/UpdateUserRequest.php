<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'cid' => ['prohibited'],
            'first_name' => ['sometimes', 'required'],
            'last_name' => ['sometimes', 'required'],
            'email' => ['sometimes', 'required', 'email', 'max:254'],
            'rating' => ['prohibited'],
            'operating_initials' => ['sometimes', 'required'],
            'roles' => ['sometimes', 'nullable'],
            'certifications' => ['sometimes', 'exists:certifications,code'],
        ];
    }
}
