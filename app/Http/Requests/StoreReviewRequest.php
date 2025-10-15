<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            //
            'title'=> 'required|between:3,25|regex:/^[a-z\d\-_\s]+$/i',
            'description'=> 'required|between:10,80|regex:/^[a-z\d\-_.\s]+$/i',
            'slug'=> ['required','string','min:5','max:30'],
            'categories_id'=>['required'],

        ];
    }
}
