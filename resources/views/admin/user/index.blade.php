@extends('layouts.admin')

@section('body')
    <div class="flex space-x-2 items-center">
        <span class="bi bi-gear text-2xl"></span>
        <h2 class="text-xl">Users</h2>
    </div>
    <div class="mt-4 bg-white">
        <div class="bg-green-500  p-2 flex justify-between">
            <h2 class="text-white">Users</h2>
            <div class="text-white"></div>
            <a class="p-2 bg-white rounded-md text-xs" href="{{ route('admin.user.create') }}">Add User</a>
        </div>
        <div class="px-4 pb-2">
            <div class="overflow-x-auto mt-2">
                <table class="min-w-full divide-y divide-gray-200 table-striped table-bordered" id="dataTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time Zone</th>

                                <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role</th>
                                <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Currency</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($collection as $item)
                            <tr class="border-b-2">
                                <td class="px-6 py-1 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->id }}
                                </td>
                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->name }}
                                </td>
                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->email }}
                                </td>

                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->time_zone }}
                                </td>
                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    @foreach ($item->roles as $role)
                                        <span class="bg-gray-200 text-gray-500 rounded-full px-2 py-1 text-xs">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->currency }}
                                </td>
                                <td class="px-6 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->updated_at }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
