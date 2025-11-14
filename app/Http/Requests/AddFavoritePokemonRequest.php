<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddFavoritePokemonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'pokemon_id' => $this->route('pokemon'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pokemon_id' => ['required', 'integer', 'min:1', 'max:1328'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pokemon_id.required' => 'Pokemon ID is required',
            'pokemon_id.integer' => 'Pokemon ID must be an integer',
            'pokemon_id.min' => 'Pokemon ID must be at least 1',
            'pokemon_id.max' => 'Pokemon ID must not exceed 1328',
        ];
    }
}

