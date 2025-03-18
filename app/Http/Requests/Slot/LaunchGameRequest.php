<?php

namespace App\Http\Requests\Slot;

use Illuminate\Foundation\Http\FormRequest;

class LaunchGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_code' => 'required|string',
            'launch_demo' => 'sometimes|boolean',
        ];
    }
}
