<?php

namespace App\Http\Requests;

use App\Enum\SharedPostType;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'url_image' => ['required', 'image', 'max:2048'],
            'caption' => [ 'max:255'],
            'type' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'shared_with' => ['array'],
            'shared_with.*' => ['integer', 'exists:users,id'],
        ];
    }
}
