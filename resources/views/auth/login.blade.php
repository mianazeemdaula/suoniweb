@extends('layouts.web')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-center items-center h-screen">
            <div class="w-full max-w-md">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <h1 class="text-2xl font-bold text-center mb-4">Login</h1>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                autofocus>
                            @error('email')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                                Password
                            </label>
                            <input
                                class="shadow appearance-none border border-red rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                                id="password" type="password" name="password" required autocomplete="current-password">
                            @error('password')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items  -center justify-between">
                            <button
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                type="submit">
                                Sign In
                            </button>
                            {{-- <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
                                href="{{ route('password.request') }}">
                                Forgot Password?
                            </a> <br>
                            <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800"
                                href="{{ route('register') }}">
                                Register
                            </a> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection