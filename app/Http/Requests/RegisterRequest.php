<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'birth_date' => ['required','date','before:' . now()->subYears(13)->format('Y-m-d'),'after:' . now()->subYears(35)->format('Y-m-d')],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed',  Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'The username field is required.',
            'name.unique'     => 'This username is already taken.',
            'birth_date.required' => 'The birth date is required.',
            'birth_date.before'  => 'You must be at least 13 years old.',
            'birth_date.after' => 'You cannot be older than 35 years.',
            'email.required'      => 'The email field is required.',
            'email.email'         => 'The email format is invalid.',
            'email.unique'        => 'This email is already registered.',
            'password.required'   => 'The password field is required.',
            'password.min'        => 'The password must be at least 8 characters.',
            'password.confirmed'  => 'Password confirmation does not match.',
        ];
    }

    /**
     * Noms d'attributs personnalisés (optionnel)
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'birth_date' => 'date de naissance',
            'location' => 'localisation',
            'password' => 'mot de passe',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Les données fournies sont invalides',
                'errors' => $validator->errors()->messages()
            ], 422)
        );
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation personnalisée supplémentaire
            if ($this->email && str_contains($this->email, 'fake')) {
                $validator->errors()->add('email', 'Cette adresse email n\'est pas autorisée');
            }
        });
    }


}
