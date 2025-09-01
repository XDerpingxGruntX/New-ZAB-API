<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitorApplicationRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:visitor_applications,user_id'],
            'cid' => ['required', 'integer', 'exists:users,cid'],
            'first_name' => [
                'required', 'string', 'max:254',
                Rule::exists('users', 'first_name')->where(fn ($query) => $query->where('cid', $this->cid)),
            ],
            'last_name' => [
                'required', 'string', 'max:254',
                Rule::exists('users', 'last_name')->where(fn ($query) => $query->where('cid', $this->cid)),
            ],
            'email' => ['required', 'email', 'max:254'],
            'justification' => ['required'],
            'home_facility' => ['required'],
            'accepted_at' => ['prohibited'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'You have already submitted a visitor application.',
        ];
    }
}
