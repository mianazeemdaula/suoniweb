<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attach Bank Account</title>
    @vite('resources/css/app.css')
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    {{-- center of the screen using tailwind --}}
    <div class="flex justify-center ">
        <div class="bg-white shadow-md rounded p-4">
            <form action="{{ url('/api/stripe-connect/') }}" method="post">
                @csrf
                <input type="hidden" name="gateway_id" value="{{ $method }}">
                <input type="hidden" name="user" value="{{ $user }}">
                <button type="submit"  class=" font-bold py-2 px-4 rounded">
                    Connect Account
                </button>
            </form>
        </div>
    </div>
</body>
</html>