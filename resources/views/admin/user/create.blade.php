@extends('layouts.admin')
@section('body')
    <div class="container px-6">
        <!--welcome  -->
        <div class="flex items-center py-6">
            <div class="flex-1">
                <div class="h2">Welcome dear {{ Auth::user()->name }}!</div>
                <div class="bread-crumb">
                    <a href="{{ url('admin') }}" class="link">Home</a>
                    <div>/</div>
                    <div>User</div>
                </div>
            </div>
            <div class="text-slate-500">{{ today()->format('d/m/Y') }}</div>
        </div>

        <!-- middle content panel starts-->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-8 md:gap-x-8 mt-8">
            <div class="relative col-span-2">
                <!-- user has already taken some course -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <p><strong>Opps Something went wrong</strong></p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- if student successfully cretated -->
                @if (session('success'))
                    <div class="alert-success mb-8">
                        <i class="bi-emoji-smile text-[24px] mr-4"></i>
                        {{ session('success') }}
                    </div>
                @endif
                <div class="p-6 rounded-lg  bg-white">
                    <div class="h2">Create Invoice</div>
                    <div class="text-slate-500 mt-1">Please provide following information</div>
                    <form action="{{ route('admin.users.store') }}" method='post' class="flex flex-col w-full mt-4"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 mt-3 text-slate-600 gap-4">
                            <div class="flex flex-col">
                                <label for="">Name*</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="border border-gray-300 p-2 rounded-lg" placeholder="Name">
                                @error('name')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="">Email*</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="border border-gray-300 p-2 rounded-lg" placeholder="Email">
                                @error('email')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="">Password*</label>
                                <input type="password" name="password" value="{{ old('password') }}"
                                    class="border border-gray-300 p-2 rounded-lg" placeholder="Password">
                                @error('password')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col">
                                <label for="">Role*</label>
                                <select name="role" id="role" class="border border-gray-300 p-2 rounded-lg">
                                    <option value="admin">Admin</option>
                                    <option value="merchant">Merchant</option>
                                    <option value="user">User</option>
                                </select>
                                @error('role')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="bg-green-500 p-2 rounded text-white">Submit Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <div class="p-6 bg-white">
                </div>
            </div>

        </div>
    </div>
@endsection
