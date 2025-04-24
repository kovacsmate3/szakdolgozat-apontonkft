<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['string', 'max:255'],
            'location_type' => ['string', 'in:partner,telephely,töltőállomás,bolt,egyéb'],
            'is_headquarter' => ['boolean'],
        ];

        // Ha létrehozási kérés, minden mező kötelező
        if ($this->isMethod('post')) {
            $rules['name'][] = 'required';
            $rules['location_type'][] = 'required';
        } else {
            // Frissítésnél csak a megadott mezők szükségesek
            $rules = array_map(function ($rule) {
                return array_merge(['sometimes'], $rule);
            }, $rules);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A helyszín nevének megadása kötelező.',
            'name.string' => 'A helyszín neve csak szöveg formátumú lehet.',
            'name.max' => 'A helyszín neve maximum 255 karakter hosszú lehet.',

            'location_type.required' => 'A helyszín típusának megadása kötelező.',
            'location_type.string' => 'A helyszín típusa csak szöveg formátumú lehet.',
            'location_type.in' => 'A helyszín típusa csak a következők egyike lehet: partner, telephely, töltőállomás, bolt, egyéb.',

            'is_headquarter.boolean' => 'A központi telephely megjelölés csak igaz vagy hamis értéket vehet fel.',
        ];
    }
}
