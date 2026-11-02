<div class="p-6">

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg shadow-sm border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Employees List</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage and monitor all company employee records.</p>
        </div>
        {{-- Add new Employee --}}
        @can('create employee')
            <button wire:click="openModal" type="button"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow flex items-center gap-2 transition-colors w-full md:w-auto justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Employee
            </button>
        @endcan
    </div>

    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search Employee</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, ID or NIC..."
                    class="w-full pl-9 pr-4 border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="relative w-full md:w-120">

            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                Filter by Position
            </label>

            <button type="button" wire:click="$toggle('isPositionDropdownOpen')"
                class="w-full text-left border border-gray-300 rounded-lg p-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500 flex justify-between items-center transition-shadow hover:border-gray-300 font-medium">

                <span class="text-gray-800 truncate">
                    {{ $filterPosition ? $filterPosition : 'All Positions' }}
                </span>

                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isPositionDropdownOpen ? 'rotate-180' : '' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if ($isPositionDropdownOpen)

                <div wire:click="$set('isPositionDropdownOpen', false)" class="fixed inset-0 z-30 cursor-default"></div>

                <div
                    class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg font-medium py-1 max-h-60 overflow-y-auto">

                    <div wire:click="selectFilterPosition('')"
                        class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors {{ $filterPosition === '' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        All Positions
                    </div>

                    @foreach ($designations as $designation)
                        <div wire:click="selectFilterPosition('{{ $designation->name }}')"
                            wire:key="pos-opt-{{ $designation->id }}"
                            class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors truncate {{ $filterPosition === $designation->name ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                            {{ $designation->name }}
                        </div>
                    @endforeach

                </div>
            @endif
        </div>

        <div class="relative w-full md:w-120">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                Filter by Status
            </label>

            <button type="button" wire:click="$toggle('isFilterDropdownOpen')"
                class="w-full text-left border border-gray-300 rounded-lg p-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500 flex justify-between items-center transition-shadow hover:border-gray-300 font-medium">

                <span class="flex items-center gap-2">
                    @if ($filterStatus == 'Active')
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-800">Active</span>
                    @elseif($filterStatus == 'Inactive')
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        <span class="text-gray-800">Inactive</span>
                    @elseif($filterStatus == 'Suspended')
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-gray-800">Suspended</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-gray-800">All Statuses</span>
                    @endif
                </span>

                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isFilterDropdownOpen ? 'rotate-180' : '' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @if ($isFilterDropdownOpen)
                <div wire:click="$set('isFilterDropdownOpen', false)" class="fixed inset-0 z-30 cursor-default"></div>

                <div
                    class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg font-medium py-1">

                    <div wire:click="selectFilterStatus('')"
                        class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2 {{ $filterStatus === '' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> All Statuses
                    </div>

                    <div wire:click="selectFilterStatus('Active')"
                        class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2 {{ $filterStatus === 'Active' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
                    </div>

                    <div wire:click="selectFilterStatus('Inactive')"
                        class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2 {{ $filterStatus === 'Inactive' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Inactive
                    </div>

                    <div wire:click="selectFilterStatus('Suspended')"
                        class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2 {{ $filterStatus === 'Suspended' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Suspended
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Emp ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Position</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Department</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse($employees as $emp)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600">{{ $emp->employee_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $emp->first_name }}
                            {{ $emp->last_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $emp->job_position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $emp->gender }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $emp->department ? $emp->department->name : 'No Department' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2.5 py-1 text-xs font-semibold rounded-full 
                            {{ $emp->status === 'Active' ? 'bg-green-100 text-green-800' : ($emp->status === 'Suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $emp->status }}
                            </span>
                        </td>

                        <td
                            class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex items-center justify-center gap-2">

                            {{-- Edit Employee Button --}}
                            @can('edit employee')
                                <button type="button" wire:click="editEmployee({{ $emp->id }})"
                                    title="Edit Employee"
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                            @endcan

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                            No employees matching the criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>
    </div>

    @if ($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col overflow-hidden">

                <div
                    class="flex justify-between items-center px-8 py-5 border-b border-gray-100 flex-shrink-0 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-green-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $isEditMode ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' }}">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                {{ $isEditMode ? 'Edit Employee Details' : 'Register New Employee' }}
                            </h3>
                            <p class="text-xs text-gray-400">Step {{ $currentStep }} of 4</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-8 py-4 bg-gray-50 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center justify-between relative">
                        <div class="absolute left-0 right-0 top-4 h-0.5 bg-gray-200 z-0 mx-8"></div>

                        <div
                            class="absolute left-0 top-4 h-0.5 bg-green-500 z-0 mx-8 transition-all duration-300
                        {{ $currentStep == 1 ? 'w-0' : ($currentStep == 2 ? 'w-1/3' : ($currentStep == 3 ? 'w-2/3' : 'w-full')) }}">
                        </div>

                        @foreach ($steps as $stepNum => $step)
                            <button wire:click="setStep({{ $stepNum }})" type="button"
                                class="flex flex-col items-center gap-1.5 z-10 group">

                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-200 shadow-sm
                            {{ $currentStep == $stepNum
                                ? 'bg-green-600 border-green-600 scale-110'
                                : ($currentStep > $stepNum
                                    ? 'bg-green-500 border-green-500'
                                    : 'bg-white border-gray-300 group-hover:border-green-400') }}">

                                    @if ($currentStep > $stepNum)
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 {{ $currentStep == $stepNum ? 'text-white' : 'text-gray-400 group-hover:text-green-400' }}"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $step['icon'] }}"></path>
                                        </svg>
                                    @endif
                                </div>

                                <span
                                    class="text-xs font-medium whitespace-nowrap
                            {{ $currentStep == $stepNum ? 'text-green-600' : ($currentStep > $stepNum ? 'text-green-400' : 'text-gray-400') }}">
                                    {{ $step['label'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <form wire:submit.prevent="saveEmployee" class="flex-1 flex flex-col overflow-hidden">
                    <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">

                        @if ($currentStep == 1)
                            <div wire:key="step-1-personal-info-form-{{ $selectedEmployeeId }}">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Personal Information & System
                                        Access</h4>
                                    <p class="text-xs text-gray-400 mb-5">Basic employee details and login permissions.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Employee
                                            ID <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="employee_id"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('employee_id')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            EPF Number <span class="text-xs text-gray-400 font-normal">(Optional /
                                                Member No)</span>
                                        </label>
                                        <input type="text" wire:model="epf_number" placeholder="e.g. D001 / 1001"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('epf_number')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Phone
                                            Number <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="phone_number"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('phone_number')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">First
                                            Name <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="first_name"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('first_name')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Last
                                            Name <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="last_name"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('last_name')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative" x-data="{ dob: @entangle('date_of_birth') }" x-init="flatpickr($refs.datePicker, { dateFormat: 'Y-m-d', defaultDate: $wire.get('date_of_birth') || dob, static: false, appendTo: document.body, onChange: function(selectedDates, dateStr) { dob = dateStr; } })"
                                        wire:ignore>

                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date
                                            of Birth <span class="text-red-500">*</span></label>

                                        <div class="relative mt-1">
                                            <input type="text" x-ref="datePicker" placeholder="YYYY-MM-DD"
                                                readonly
                                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white text-gray-800 cursor-pointer">

                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>

                                        @error('date_of_birth')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                            Gender <span class="text-red-500">*</span>
                                        </label>

                                        <button type="button" wire:click="$toggle('isGenderDropdownOpen')"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300">
                                            <span>{{ $gender ?: 'Select Gender' }}</span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isGenderDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isGenderDropdownOpen)
                                            <div wire:click="$set('isGenderDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto">
                                                <div wire:click="selectGender('Male')"
                                                    class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Male</div>
                                                <div wire:click="selectGender('Female')"
                                                    class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Female</div>
                                                <div wire:click="selectGender('Other')"
                                                    class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Other</div>
                                            </div>
                                        @endif

                                        @error('gender')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">NIC
                                            Number</label>
                                        <input type="text" wire:model="nic_number"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('nic_number')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Passport
                                            Number</label>
                                        <input type="text" wire:model="passport_number"
                                            class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('passport_number')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Custom Department Dynamic Dropdown -->
                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Department</label>
                                        <button type="button" wire:click="toggleDepartmentDropdown"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">
                                            <span
                                                class="{{ $selectedDepartmentName ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $selectedDepartmentName ?: '-- Select Department --' }}
                                            </span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isDepartmentDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isDepartmentDropdownOpen)
                                            <div wire:click="$set('isDepartmentDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium">
                                                <div wire:click="selectDepartment('', '')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-100 text-gray-400 transition-colors">
                                                    -- Select Department --
                                                </div>
                                                @foreach ($departments as $departmentItem)
                                                    <div wire:click="selectDepartment({{ $departmentItem->id }}, '{{ $departmentItem->name }}')"
                                                        wire:key="form-department-item-index-key-{{ $departmentItem->id }}"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                        {{ $departmentItem->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @error('department_id')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Work Shift Selection Dropdown  -->
                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Work
                                            Shift</label>
                                        <button type="button" wire:click="toggleShiftDropdown"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">
                                            <span
                                                class="{{ $selectedShiftName ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $selectedShiftName ?: '-- Select Shift --' }}
                                            </span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isShiftDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isShiftDropdownOpen)
                                            <div wire:click="$set('isShiftDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium">
                                                <div wire:click="selectShift('', '')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-100 text-gray-400 transition-colors">
                                                    -- Select Shift --
                                                </div>
                                                @foreach ($shifts as $shiftItem)
                                                    <div wire:click="selectShift({{ $shiftItem->id }}, '{{ $shiftItem->name }}')"
                                                        wire:key="form-shift-item-key-{{ $shiftItem->id }}"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex justify-between items-center">
                                                        <span>{{ $shiftItem->name }}</span>
                                                        <span
                                                            class="text-xs text-gray-400">({{ \Carbon\Carbon::parse($shiftItem->start_time)->format('h:i A') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($shiftItem->end_time)->format('h:i A') }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @error('shift_id')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Employee Category Selection Dropdown -->
                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                            Employee Category <span class="text-red-500">*</span>
                                        </label>
                                        <button type="button" wire:click="toggleCategoryDropdown"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">
                                            <span
                                                class="{{ $selectedCategoryName ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $selectedCategoryName ?: '-- Select Employee Category --' }}
                                            </span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isCategoryDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isCategoryDropdownOpen)
                                            <div wire:click="$set('isCategoryDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium py-1">
                                                <div wire:click="selectCategory('', '')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-100 text-gray-400 transition-colors">
                                                    -- Select Employee Category --
                                                </div>
                                                @foreach ($categories as $categoryItem)
                                                    <div wire:click="selectCategory({{ $categoryItem->id }}, '{{ $categoryItem->name }}')"
                                                        wire:key="emp-cat-item-{{ $categoryItem->id }}"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex justify-between items-center">
                                                        <span>{{ $categoryItem->name }}</span>
                                                        <span
                                                            class="text-xs font-mono text-gray-400">({{ $categoryItem->code }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @error('employee_category_id')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                                            Job Position <span class="text-red-500">*</span>
                                        </label>

                                        <button type="button" wire:click="$toggle('isJobDropdownOpen')"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300">
                                            <span class="{{ $job_position ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $job_position ?: 'Select Job Position' }}
                                            </span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 {{ $isJobDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        @if ($isJobDropdownOpen)
                                            <div wire:click="$set('isJobDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full border border-gray-200 rounded-xl text-sm bg-white shadow-lg max-h-48 overflow-y-auto">
                                                <div wire:click="selectJobPosition('')"
                                                    class="p-2.5 hover:bg-gray-100 cursor-pointer text-gray-400 transition-colors">
                                                    Select Job Position
                                                </div>
                                                @foreach ($designations as $designation)
                                                    <div wire:click="selectJobPosition('{{ $designation->name }}')"
                                                        wire:key="job-pos-{{ $designation->id }}"
                                                        class="p-2.5 hover:bg-blue-50 hover:text-blue-600 cursor-pointer text-gray-700 transition-colors">
                                                        {{ $designation->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @error('job_position')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                            Work Location <span class="text-red-500">*</span>
                                        </label>

                                        <button type="button" wire:click="$toggle('isLocationDropdownOpen')"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">
                                            <span class="{{ $work_location ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $work_location ?: '-- Select Work Location --' }}
                                            </span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isLocationDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isLocationDropdownOpen)
                                            <div wire:click="$set('isLocationDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium">
                                                <div wire:click="selectWorkLocation('')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-100 text-gray-400 transition-colors">
                                                    -- Select Work Location --
                                                </div>
                                                @foreach ($workLocations as $location)
                                                    <div wire:click="selectWorkLocation('{{ $location->name }}')"
                                                        wire:key="work-loc-{{ $location->id }}"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                        {{ $location->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @error('work_location')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative">
                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                            Marital Status
                                        </label>

                                        <button type="button" wire:click="$toggle('isMaritalDropdownOpen')"
                                            class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300">
                                            <span class="{{ $marital_status ? 'text-gray-800' : 'text-gray-400' }}">
                                                {{ $marital_status ?: 'Select Status' }}
                                            </span>
                                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isMaritalDropdownOpen ? 'rotate-180' : '' }}"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        @if ($isMaritalDropdownOpen)
                                            <div wire:click="$set('isMaritalDropdownOpen', false)"
                                                class="fixed inset-0 z-30 cursor-default"></div>
                                            <div
                                                class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                                <div wire:click="selectMaritalStatus('Single')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Single</div>
                                                <div wire:click="selectMaritalStatus('Married')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Married</div>
                                                <div wire:click="selectMaritalStatus('Divorced')"
                                                    class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors">
                                                    Divorced</div>
                                            </div>
                                        @endif

                                        @error('marital_status')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="relative" x-data="{ doj: @entangle('date_of_join') }" x-init="flatpickr($refs.datePicker, { dateFormat: 'Y-m-d', defaultDate: doj, onChange: function(selectedDates, dateStr) { doj = dateStr; } })"
                                        wire:ignore>

                                        <label
                                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date
                                            of Join</label>

                                        <div class="relative mt-1">
                                            <input type="text" x-ref="datePicker" placeholder="YYYY-MM-DD"
                                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white text-gray-800 cursor-pointer">

                                            <div
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>

                                        @error('date_of_join')
                                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- OT Eligibility Checkbox (Category Gatekeeper Guard - Clean Blade) -->
                                    <div
                                        class="md:col-span-2 flex items-center justify-between bg-white border border-gray-200 p-3.5 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" id="is_ot_eligible" wire:model="is_ot_eligible"
                                                @disabled(!$isCategoryOtEnabled)
                                                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300 {{ !$isCategoryOtEnabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                            <label for="is_ot_eligible"
                                                class="text-sm font-semibold {{ $isCategoryOtEnabled ? 'text-gray-700 cursor-pointer' : 'text-gray-400' }}">
                                                Eligible for Overtime (OT Calculation)
                                            </label>
                                        </div>
                                        @if (!$isCategoryOtEnabled)
                                            <span
                                                class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                                                Disabled: This category is non-OT eligible
                                            </span>
                                        @endif
                                    </div>

                                    <!-- System Access Container (Email, Password, Status) -->
                                    <div wire:key="email-status-container-{{ $selectedEmployeeId }}"
                                        class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 bg-blue-50/20 p-5 rounded-2xl border border-blue-100/50 mt-2">
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Login
                                                Email Address <span class="text-red-500">*</span></label>
                                            <input type="email" wire:model="email"
                                                wire:key="employee-login-email-input-field"
                                                placeholder="employee@company.com"
                                                class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow">
                                            @error('email')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Password Field -->
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">System
                                                Password</label>
                                            <input type="password" wire:model="password"
                                                wire:key="employee-login-password-input-field"
                                                placeholder="{{ $isEditMode ? 'Leave blank to keep current' : 'Default: password123' }}"
                                                class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow">
                                            @error('password')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="relative">
                                            <label
                                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                                Employee Status
                                            </label>

                                            <button type="button" wire:click="$toggle('isStatusDropdownOpen')"
                                                class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">
                                                <span class="flex items-center gap-2">
                                                    @if ($status == 'Active')
                                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                    @elseif($status == 'Inactive')
                                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                                    @elseif($status == 'Suspended')
                                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                    @endif

                                                    <span class="{{ $status ? 'text-gray-800' : 'text-gray-400' }}">
                                                        {{ $status ?: 'Select Status' }}
                                                    </span>
                                                </span>

                                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isStatusDropdownOpen ? 'rotate-180' : '' }}"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            @if ($isStatusDropdownOpen)
                                                <div wire:click="$set('isStatusDropdownOpen', false)"
                                                    class="fixed inset-0 z-30 cursor-default"></div>
                                                <div
                                                    class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium">
                                                    <div wire:click="selectStatus('Active')"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
                                                    </div>
                                                    <div wire:click="selectStatus('Inactive')"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span> Inactive
                                                    </div>
                                                    <div wire:click="selectStatus('Suspended')"
                                                        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 text-gray-700 transition-colors flex items-center gap-2">
                                                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Suspended
                                                    </div>
                                                </div>
                                            @endif

                                            @error('status')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($currentStep == 2)
                            <div wire:key="step-2-address-form-{{ $selectedEmployeeId }}">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Address & Emergency Contact
                                    </h4>
                                    <p class="text-xs text-gray-400 mb-5">Residential address and emergency contact
                                        details.</p>
                                </div>
                                <div class="space-y-6">
                                    {{-- Residential Address Section --}}
                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                        <h4
                                            class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                            <span
                                                class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                                    </path>
                                                </svg>
                                            </span>
                                            Residential Address
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <!-- CHANGED: Made the asterisk red -->
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Address
                                                    Line 1 <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model.live="address_line_1"
                                                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                                @error('address_line_1')
                                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Address
                                                    Line 2</label>
                                                <input type="text" wire:model.live="address_line_2"
                                                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                            </div>

                                            <div>
                                                <!-- CHANGED: Made the asterisk red -->
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">City
                                                    <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model.live="city"
                                                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                                @error('city')
                                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Postal
                                                    Code</label>
                                                <input type="text" wire:model.live="postal_code"
                                                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Emergency Contacts Header & Add Button --}}
                                    <div class="flex justify-between items-center mt-6">
                                        <h4
                                            class="text-xs font-bold text-red-600 uppercase tracking-wider flex items-center gap-2">
                                            <span
                                                class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-red-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                    </path>
                                                </svg>
                                            </span>
                                            Emergency Contacts
                                        </h4>
                                        <button type="button" wire:click="addEmergencyContact"
                                            class="flex items-center gap-1.5 text-xs bg-red-600 text-white font-medium py-1.5 px-3 rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Contact
                                        </button>
                                    </div>

                                    {{-- Multiple Emergency Contacts Loop --}}
                                    @foreach ($emergency_contacts as $index => $contact)
                                        <div wire:key="emergency-contact-{{ $index }}"
                                            class="bg-red-50 rounded-2xl p-5 border border-red-100 relative mb-4">
                                            <div
                                                class="absolute -top-2 -left-2 w-6 h-6 bg-red-600 text-white text-xs rounded-full flex items-center justify-center font-bold shadow">
                                                {{ $index + 1 }}</div>

                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                                {{-- Contact Name --}}
                                                <div class="w-full">
                                                    <!-- CHANGED: Made the asterisk red -->
                                                    <label
                                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Contact
                                                        Name <span class="text-red-500">*</span></label>
                                                    <input type="text"
                                                        wire:model.live="emergency_contacts.{{ $index }}.name"
                                                        class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                                    @error("emergency_contacts.{$index}.name")
                                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                {{-- Relationship --}}
                                                <div class="w-full">
                                                    <!-- CHANGED: Made the asterisk red -->
                                                    <label
                                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Relationship
                                                        <span class="text-red-500">*</span></label>
                                                    <input type="text"
                                                        wire:model.live="emergency_contacts.{{ $index }}.relationship"
                                                        class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                                    @error("emergency_contacts.{$index}.relationship")
                                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                {{-- Phone Number --}}
                                                <div class="w-full">
                                                    <!-- CHANGED: Made the asterisk red -->
                                                    <label
                                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Phone
                                                        Number <span class="text-red-500">*</span></label>
                                                    <input type="text"
                                                        wire:model.live="emergency_contacts.{{ $index }}.phone"
                                                        class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                                    @error("emergency_contacts.{$index}.phone")
                                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                {{-- Delete Button (Only shows if there's more than 1 contact) --}}
                                                <div class="w-full md:w-auto flex justify-start md:justify-end mb-1">
                                                    @if (count($emergency_contacts) > 1)
                                                        <button type="button"
                                                            wire:click="removeEmergencyContact({{ $index }})"
                                                            title="Remove Contact"
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="2"
                                                                stroke="currentColor" class="w-4 h-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($currentStep == 3)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-1">Family Details</h4>
                                <p class="text-xs text-gray-400 mb-5">Spouse, Parent/Guardian information and children
                                    details.</p>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                    <h4
                                        class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </span>
                                        @if ($marital_status === 'Married')
                                            Spouse Details
                                        @else
                                            Parent / Guardian Details
                                        @endif
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <!-- CHANGED: Made the asterisk red -->
                                            <label
                                                class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">
                                                @if ($marital_status === 'Married')
                                                    Spouse Name
                                                @else
                                                    Parent / Guardian Name
                                                @endif <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model.live="spouse_name"
                                                class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                            @error('spouse_name')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="relative" x-data="{ spouseDob: @entangle('spouse_dob') }" x-init="flatpickr($refs.spouseDatePicker, { dateFormat: 'Y-m-d', defaultDate: spouseDob, onChange: function(selectedDates, dateStr) { spouseDob = dateStr; } })"
                                            wire:ignore>

                                            <label
                                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date
                                                of Birth</label>

                                            <div class="relative mt-1">
                                                <input type="text" x-ref="spouseDatePicker"
                                                    placeholder="YYYY-MM-DD"
                                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white text-gray-800 cursor-pointer">

                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            </div>

                                            @error('spouse_dob')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">NIC
                                                Number</label>
                                            <input type="text" wire:model.live="spouse_nic"
                                                class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                            @error('spouse_nic')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Passport
                                                Number</label>
                                            <input type="text" wire:model.live="spouse_passport"
                                                class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                            @error('spouse_passport')
                                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    </div>
                                </div>

                                @if (in_array($marital_status, ['Married', 'Divorced']))
                                    <div class="flex justify-between items-center">
                                        <h4
                                            class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                                            <span
                                                class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-blue-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                                    </path>
                                                </svg>
                                            </span>
                                            Children Details
                                        </h4>
                                        <button type="button" wire:click="addChild"
                                            class="flex items-center gap-1.5 text-xs bg-blue-600 text-white font-medium py-1.5 px-3 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Child
                                        </button>
                                    </div>

                                    @foreach ($children as $index => $child)
                                        <div wire:key="child-form-{{ $index }}"
                                            class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 relative">
                                            <div
                                                class="absolute -top-2 -left-2 w-6 h-6 bg-blue-600 text-white text-xs rounded-full flex items-center justify-center font-bold shadow">
                                                {{ $index + 1 }}</div>

                                            <div>
                                                <label
                                                    class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Child
                                                    Name</label>
                                                <input type="text"
                                                    wire:model.live="children.{{ $index }}.name"
                                                    class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                @error("children.{$index}.name")
                                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="relative" wire:key="child-dob-{{ $index }}"
                                                x-data="{ childDob: @entangle('children.' . $index . '.date_of_birth') }" x-init="flatpickr($refs.childDatePicker, { dateFormat: 'Y-m-d', defaultDate: childDob, onChange: function(selectedDates, dateStr) { childDob = dateStr; } })" wire:ignore>

                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Birthday</label>

                                                <div class="relative mt-1">
                                                    <input type="text" x-ref="childDatePicker"
                                                        placeholder="YYYY-MM-DD"
                                                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white text-gray-800 cursor-pointer">

                                                    <div
                                                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </div>

                                                @error("children.{$index}.date_of_birth")
                                                    <span
                                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="relative {{ $openChildGenderIndex === $index ? 'z-[60]' : 'z-10' }}"
                                                wire:key="child-gender-{{ $index }}">

                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Gender</label>

                                                <button type="button"
                                                    wire:click="$set('openChildGenderIndex', {{ $openChildGenderIndex === $index ? 'null' : $index }})"
                                                    class="mt-1 w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center transition-shadow hover:border-gray-300 font-medium">

                                                    <span
                                                        class="{{ isset($children[$index]['gender']) && $children[$index]['gender'] ? 'text-gray-800' : 'text-gray-400' }}">
                                                        {{ $children[$index]['gender'] ?? 'Select' }}
                                                    </span>

                                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $openChildGenderIndex === $index ? 'rotate-180' : '' }}"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                @if ($openChildGenderIndex === $index)
                                                    <div wire:click="$set('openChildGenderIndex', null)"
                                                        class="fixed inset-0 z-30 cursor-default"></div>

                                                    <div
                                                        class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-60 overflow-auto font-medium py-1">

                                                        <div wire:click="selectChildGender({{ $index }}, 'Male')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ isset($children[$index]['gender']) && $children[$index]['gender'] === 'Male' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Male
                                                        </div>

                                                        <div wire:click="selectChildGender({{ $index }}, 'Female')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ isset($children[$index]['gender']) && $children[$index]['gender'] === 'Female' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Female
                                                        </div>

                                                        <div wire:click="selectChildGender({{ $index }}, 'Other')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ isset($children[$index]['gender']) && $children[$index]['gender'] === 'Other' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Other
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-end justify-between gap-2">

                                                <div class="relative {{ $openChildMaritalIndex === $index ? 'z-[60]' : 'z-10' }}"
                                                    wire:key="child-marital-{{ $index }}">

                                                    <label
                                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Marital
                                                        Status</label>

                                                    <button type="button"
                                                        wire:click="$set('openChildMaritalIndex', {{ $openChildMaritalIndex === $index ? 'null' : $index }})"
                                                        class="mt-1 w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center transition-shadow hover:border-gray-300 font-medium">

                                                        <span
                                                            class="{{ isset($children[$index]['marital_status']) && $children[$index]['marital_status'] ? 'text-gray-800' : 'text-gray-400' }}">
                                                            {{ $children[$index]['marital_status'] ?? 'Single' }}
                                                        </span>

                                                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $openChildMaritalIndex === $index ? 'rotate-180' : '' }}"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>

                                                    @if ($openChildMaritalIndex === $index)
                                                        <div wire:click="$set('openChildMaritalIndex', null)"
                                                            class="fixed inset-0 z-30 cursor-default"></div>

                                                        <div
                                                            class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-60 overflow-auto font-medium py-1">

                                                            <div wire:click="selectChildMaritalStatus({{ $index }}, 'Single')"
                                                                class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ isset($children[$index]['marital_status']) && $children[$index]['marital_status'] === 'Single' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                                Single
                                                            </div>

                                                            <div wire:click="selectChildMaritalStatus({{ $index }}, 'Married')"
                                                                class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ isset($children[$index]['marital_status']) && $children[$index]['marital_status'] === 'Married' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                                Married
                                                            </div>

                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Remove Child Button --}}
                                                <button type="button" wire:click="removeChild({{ $index }})"
                                                    title="Remove Child"
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div
                                        class="flex items-center gap-3 p-4 bg-amber-50 text-amber-700 text-sm rounded-xl border border-amber-200">
                                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Children registration is only available for <strong>Married</strong>
                                            employees.</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($currentStep == 4)
                            <div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Salary & Bank Details</h4>
                                    <p class="text-xs text-gray-400 mb-5">Compensation structure and payment remittance
                                        information.</p>
                                </div>

                                @if ($isBankDropdownOpen || $isBranchDropdownOpen)
                                    <div wire:click="$set('isBankDropdownOpen', false); $set('isBranchDropdownOpen', false);"
                                        class="fixed inset-0 z-40 bg-transparent"></div>
                                @endif

                                <div class="space-y-6">
                                    <div
                                        class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-5 border border-green-100">
                                        <h4
                                            class="text-xs font-bold text-green-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                                            <span
                                                class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-green-700" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                            </span>
                                            Salary Structure
                                        </h4>
                                        <div class="max-w-xs">
                                            <!-- CHANGED: Made the asterisk red -->
                                            <label
                                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Basic
                                                Salary <span class="text-red-500">*</span></label>
                                            <div class="relative mt-1">
                                                <span
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">LKR</span>
                                                <input type="number" step="0.01" wire:model="basic_salary"
                                                    class="w-full border border-green-200 bg-white rounded-xl pl-12 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow font-semibold text-gray-800">
                                            </div>
                                            @error('basic_salary')
                                                <span class="text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative z-50">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                            <div class="relative">
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                                    Payment Method <span class="text-red-500">*</span>
                                                </label>

                                                <button type="button" wire:click="$toggle('isPaymentDropdownOpen')"
                                                    class="w-full text-left border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 flex justify-between items-center mt-1 transition-shadow hover:border-gray-300 font-medium">

                                                    <span
                                                        class="{{ $payment_method ? 'text-gray-800' : 'text-gray-400' }}">
                                                        {{ $payment_method ?: 'Select Payment Method' }}
                                                    </span>

                                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isPaymentDropdownOpen ? 'rotate-180' : '' }}"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>

                                                @if ($isPaymentDropdownOpen)
                                                    <div wire:click="$set('isPaymentDropdownOpen', false)"
                                                        class="fixed inset-0 z-30 cursor-default"></div>

                                                    <div
                                                        class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto font-medium py-1">

                                                        <div wire:click="selectPaymentMethod('Bank Transfer')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ $payment_method === 'Bank Transfer' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Bank Transfer
                                                        </div>

                                                        <div wire:click="selectPaymentMethod('Cash')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ $payment_method === 'Cash' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Cash
                                                        </div>

                                                        <div wire:click="selectPaymentMethod('Cheque')"
                                                            class="cursor-pointer px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors {{ $payment_method === 'Cheque' ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                                            Cheque
                                                        </div>
                                                    </div>
                                                @endif

                                                @error('payment_method')
                                                    <span
                                                        class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div>
                                                <!-- CHANGED: Added a red asterisk -->
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account
                                                    Holder <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model="account_holder_name"
                                                    class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                            </div>

                                            <div class="relative">
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                                    Bank Name <span class="text-red-500">*</span>
                                                </label>

                                                <div class="relative mt-1">
                                                    <input type="text" wire:model.live="searchBank"
                                                        wire:focus="$set('isBankDropdownOpen', true)"
                                                        placeholder="Type to search bank..."
                                                        class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow font-medium text-gray-800">
                                                </div>

                                                @if ($isBankDropdownOpen)
                                                    <div wire:click="$set('isBankDropdownOpen', false)"
                                                        class="fixed inset-0 z-30 cursor-default"></div>

                                                    <div
                                                        class="absolute z-40 mt-1 w-full border border-gray-200 rounded-xl text-sm bg-white shadow-xl max-h-48 overflow-y-auto font-medium py-1">

                                                        @if ($filteredBanks->isEmpty())
                                                            <div class="p-3 text-gray-400 italic text-center">No banks
                                                                found.</div>
                                                        @else
                                                            @foreach ($filteredBanks as $bank)
                                                                <div wire:click="selectBank('{{ $bank['bank_name'] }}', {{ $bank['id'] }})"
                                                                    wire:key="bank-opt-{{ $bank['id'] }}"
                                                                    class="px-4 py-2.5 hover:bg-blue-50 hover:text-blue-600 cursor-pointer border-b border-gray-50 last:border-none transition-colors text-gray-700">
                                                                    {{ $bank['bank_name'] }}
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endif

                                                @error('bank_name')
                                                    <span
                                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div
                                                class="relative {{ $isBranchDropdownOpen && $bank_name && trim($bank_name) !== '' ? 'z-[60]' : 'z-10' }}">

                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                                    Branch Name <span class="text-red-500">*</span>
                                                </label>

                                                <div class="relative mt-1">
                                                    <input type="text" wire:model.live="searchBranch"
                                                        wire:focus="{{ $bank_name && trim($bank_name) !== '' ? '$set(\'isBranchDropdownOpen\', true)' : '' }}"
                                                        placeholder="{{ $bank_name && trim($bank_name) !== '' ? 'Type to search Branch...' : 'Select a bank first' }}"
                                                        {{ $bank_name && trim($bank_name) !== '' ? '' : 'disabled' }}
                                                        class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 transition-shadow font-medium text-gray-800">
                                                </div>

                                                @if ($isBranchDropdownOpen && $bank_name && trim($bank_name) !== '')
                                                    <div wire:click="$set('isBranchDropdownOpen', false)"
                                                        class="fixed inset-0 z-30 cursor-default"></div>

                                                    <div
                                                        class="absolute z-40 mt-1 w-full border border-gray-200 rounded-xl text-sm bg-white shadow-xl max-h-48 overflow-y-auto font-medium py-1">

                                                        @if ($filteredBranches->isEmpty())
                                                            <div class="p-3 text-gray-400 italic text-center">No
                                                                branches found for this bank.</div>
                                                        @else
                                                            @foreach ($filteredBranches as $branch)
                                                                <div wire:click="selectBranch('{{ $branch['branch_name'] }}')"
                                                                    wire:key="branch-opt-{{ $loop->index }}"
                                                                    class="px-4 py-2.5 hover:bg-blue-50 hover:text-blue-600 cursor-pointer border-b border-gray-50 last:border-none transition-colors text-gray-700">
                                                                    {{ $branch['branch_name'] }}
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endif

                                                @error('branch_name')
                                                    <span
                                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div>
                                                <!-- CHANGED: Added a red asterisk -->
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account
                                                    Number <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model="account_number"
                                                    class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                                @error('account_number')
                                                    <span
                                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div
                            class="px-8 py-4 bg-white border-t border-gray-100 flex justify-between items-center flex-shrink-0">
                            <div>
                                @if ($currentStep > 1)
                                    <button wire:click="setStep({{ $currentStep - 1 }})" type="button"
                                        class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-5 rounded-xl transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Back
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400">{{ $currentStep }} / 4</span>
                                @if ($currentStep < 4)
                                    <button wire:click="setStep({{ $currentStep + 1 }})" type="button"
                                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm">
                                        Save & Continue
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button type="submit"
                                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $isEditMode ? 'Update Employee' : 'Save & Register' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                </form>
            </div>
        </div>
    @endif

</div>
