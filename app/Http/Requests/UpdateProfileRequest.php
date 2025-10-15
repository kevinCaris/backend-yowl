<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


class UpdateProfileRequest extends FormRequest
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
    public function rules()
    {
        $user = auth()->user();

        // Si c'est un changement de mot de passe
        if ($this->has('old_password')) {
            return [
                'old_password' => 'required',
                'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ];
        }
        // Sinon, mise à jour du profil
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'birth_date' => ['sometimes', 'date', 'before:' . now()->subYears(13)->format('Y-m-d')],
            'location' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable','string'], // 2MB max
        ];
    }

    public function messages()
    {
        return [
            // Password messages
            'old_password.required' => 'Your current password is required.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The password confirmation does not match.',

            // Name messages
            'name.string' => 'Your name must be valid text.',
            'name.max' => 'Your name cannot exceed 255 characters.',

            // Email messages
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use by another account.',

            // Birth date messages
            'birth_date.date' => 'Please provide a valid date for your birthday.',
            'birth_date.before' => 'You must be at least 13 years old to use this service.',

            // Location messages
            'location.string' => 'Your location must be valid text.',
            'location.max' => 'Your location cannot exceed 255 characters.',

            // Avatar messages
            'avatar.string' => 'Error on the file loading.',
            // 'avatar.mimes' => 'The avatar must be a file of type: jpeg, jpg, png, gif, or webp.',
            // 'avatar.max' => 'The avatar size cannot exceed 2MB.',
        ];
    }

    public function attributes()
    {
        return [
            'old_password' => 'current password',
            'password' => 'new password',
            'birth_date' => 'birthday',
        ];
    }

}
