<?php

namespace App\Http\Requests;

use App\Enum\MessageType;
use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends FormRequest
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
            'conversation_id' => 'nullable|exists:conversations,id',
            'user_id' => 'nullable|exists:users,id',
            'text' => 'nullable|string',
            'url_image' => 'nullable|string',
            'message_type' => 'required|in:' . implode(',', MessageType::getValues()),
            'height' => ['nullable', 'double'], 
            'width' => ['nullable', 'double'],
        ];
    }
}
