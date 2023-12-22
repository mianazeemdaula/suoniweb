<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Shop CodeCryptex</title>
    <style>
        body {
            font-family: 'Poppins-Regular', sans-serif;
        }
    </style>
    @yield('header')
</head>

<body>
    @php
        $page = Route::currentRouteName();
    @endphp
    <header class="header">
        <div class="container">
            <div class="flex flex-wrap h-16 items-center justify-between w-full bg-white">
                <img src="{{ asset('/images/logo/original.png') }}" alt="" class="w-40">
                <div class="flex items-center space-x-4">
                    <a href='' class="relative">
                        <i class="bi bi-bell"></i>
                        <div class="absolute flex justify-center items-center h-3 w-3 top-0 right-0 rounded-full">
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75">
                                <img src="{{ asset('/images/.png') }}" alt="" class="w-8">
                            </span>
                        </div>
                    </a>
                    <div
                        class="flex justify-center items-center w-8 h-8 rounded-full ring-1 ring-sky-500 ring-offset-2">
                        <!-- <img src="{{ asset('/images/all-img/team/team-2.png') }}" alt="" class="w-6"> -->
                        <i class="bi-person"></i>
                    </div>
                    <div class='md:hidden hover:cursor-pointer' onclick="toggleSidebar()">
                        <i class="bi bi-list text-[32px]"></i>
                    </div>

                </div>
            </div>
        </div>

    </header>

    <aside aria-label="Sidebar" id='sidebar' class="sidebar">
        <div class="absolute top-2 right-2 hover:cursor-pointer md:hidden text-slate-800" onclick="toggleSidebar()"><i
                class="bi bi-x text-[32px]"></i></div>
        <div class="flex justify-center items-center bg-green-50 py-12 md:hidden">
            <img src="{{ asset('/images/logo/black.png') }}" alt="" class="w-40">
        </div>
        <div class="mt-12 px-8">
            <ul class="flex flex-col items-start space-y-4">
                <li @if ($page == 'home') class="active" @endif>
                    <i class="bi-grid"></i>
                    <a href="{{ url('dashboard/') }}">Home</a>
                </li>
                <li @if ($page == 'invoices') class="active" @endif>
                    <i class="bi-receipt"></i>
                    <a href="{{ route('user.invoices.index') }}">Payments</a>
                </li>

                @role('admin')
                <li @if ($page == 'users') class="active" @endif>
                    <i class="bi-people-fill"></i>
                    <a href="{{ route('admin.users.index') }}">Users</a>
                </li>
                @endrole
                <li>
                    <i class="bi-power"></i>
                    <a href="{{ url('signout') }}">Signout</a>
                </li>

            </ul>
        </div>
    </aside>
    <div class="responsive-body">
        <div class="container px-6 py-2">
            @yield('body')
        </div>
    </div>
    @yield('script')
    <script type="module">
        @if (Session::has('alert'))
            Swal.fire(
                "Message Alert",
                "{{ Session::get('message') }}",
                "{{ Session::get('alert') ? 'success' : 'error' }}"
            )
        @endif
    </script>

    <script>
        function toggleSidebar() {
            $("#sidebar").toggleClass("mobile");
        }
    </script>
</body>

</html>
