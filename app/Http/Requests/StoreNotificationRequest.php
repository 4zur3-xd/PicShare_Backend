<?php

namespace App\Http\Requests;

use App\Enum\NotificationPayloadType;
use App\Enum\NotificationType;
use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'content' => 'nullable|string',
            'title' => 'required|string',
            'link_to' => 'required|array',
            'link_to.link_to_type' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, NotificationPayloadType::getValues())) {
                        $fail($attribute . ' is invalid.');
                    }
                },
            ],
            'link_to.post_id' => 'nullable|integer',
            'link_to.comment_id' => 'nullable|integer',
            'link_to.reply_id' => 'nullable|integer',
            'link_to.friend_type' => 'nullable', 
            'notification_type' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, NotificationType::getValues())) {
                        $fail($attribute . ' is invalid.');
                    }
                },
            ],
        ];
    }
}
