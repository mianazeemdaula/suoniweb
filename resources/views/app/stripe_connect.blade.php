<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bank Account</title>
    @vite('resources/css/app.css')
</head>
<body>
    {{-- center of the screen using tailwind --}}
    <div class="flex justify-center h-screen">
        <div class="w-1/2 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <a href="suoni://app.gleedu.com/bank/{{ $account }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Connect with Stripe
                </a>
        </div>
</body>
</html>