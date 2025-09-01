<?php

namespace App\Http\Requests;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateEventRequest extends FormRequest
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
            'name' => ['sometimes', 'required'],
            'slug' => ['sometimes'],
            'description' => ['sometimes', 'required'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after:starts_at'],
            'registration_opens_at' => ['sometimes', 'required', 'date', 'before:starts_at'],
            'registration_closes_at' => ['sometimes', 'required', 'date', 'after:registration_opens_at', 'before_or_equal:ends_at'],
            'banner' => ['sometimes', 'nullable', File::image()->max('6mb')],
            'positions' => ['sometimes', 'array'],
            'positions.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $parts = explode('_', $value);
                    if (count($parts) < 2 || count($parts) > 3) {
                        $fail('Invalid position format. Must be AIRPORT_POSITION or AIRPORT_SECTOR_POSITION.');

                        return;
                    }

                    $airport = Airport::tryFrom($parts[0]);
                    if (! $airport) {
                        $fail('Invalid airport code.');

                        return;
                    }

                    $positionType = ControllerPosition::tryFrom($parts[count($parts) - 1]);
                    if (! $positionType) {
                        $fail('Invalid position type.');

                        return;
                    }

                    if (count($parts) === 3) {
                        if (! is_numeric($parts[1])) {
                            $fail('Sector number must be numeric.');

                            return;
                        }
                    }
                },
            ],
        ];
    }
}
