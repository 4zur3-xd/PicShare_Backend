<x-mail::message>
# LOGIN EVENT

You just logged in to **{{ config('app.name') }}**.

### Details about the connection:
- **Date:** {{ now()->format('Y-m-d H:i:s') }}
- **IP Address:** {{ request()->ip() }}
- @if($deviceName)
    Device Name: {{ $deviceName }}
@endif
- @if($deviceId)
    Device ID: {{ $deviceId }}
@endif

If you don't recognize this activity, you should change your password immediately using the link below:

<x-mail::button :url="'http://192.168.5.248:8000/forgot-password'">
Reset password
</x-mail::button>

Otherwise, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>