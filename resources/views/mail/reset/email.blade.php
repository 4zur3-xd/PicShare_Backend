<x-mail::message>
# Reset Your Password

Hello,

Here is your OTP code to reset your password:

<x-mail::panel>
    <strong>{{ $otp }}</strong>
</x-mail::panel>

This code is valid for {{$validity}} minutes. Please do not share it with anyone.



Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
