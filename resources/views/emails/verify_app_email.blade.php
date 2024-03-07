<x-mail::message>
# Email Verification

Your email verification code is.

<x-mail::button :url="''">
{{ $code }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
