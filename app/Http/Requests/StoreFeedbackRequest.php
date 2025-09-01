<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ControllerPosition;
use App\Enums\FeedbackRating;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'controller_id' => ['required', 'exists:users,id'],
            'position' => ['required', Rule::enum(ControllerPosition::class)],
            'rating' => ['required', Rule::enum(FeedbackRating::class)],
            'comment' => ['required', 'string', 'max:5000'],
            'anonymous' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'controller_id.required' => 'Please select a controller.',
            'controller_id.exists' => 'The selected controller is invalid.',
            'position.required' => 'Please select a position.',
            'rating.required' => 'Please select a rating.',
            'comment.required' => 'Please provide a comment.',
            'comment.max' => 'Comment must not exceed 5000 characters.',
        ];
    }
}
