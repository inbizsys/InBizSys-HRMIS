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
        <button wire:click="openModal" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow flex items-center gap-2 transition-colors w-full md:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Employee
        </button>
    </div>

    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search Employee</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, ID or NIC..." class="w-full pl-9 pr-4 border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Filter by Position</label>
            <select wire:model.live="filterPosition" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                <option value="">All Positions</option>
                @foreach($designations as $designation)
                <option value="{{ $designation->name }}">{{ $designation->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Filter by Status</label>
            <div class="flex gap-2">
                <select wire:model.live="filterStatus" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Emp ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600">{{ $emp->employee_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $emp->job_position }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $emp->gender }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                            {{ $emp->status === 'Active' ? 'bg-green-100 text-green-800' : ($emp->status === 'Suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $emp->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <button wire:click="editEmployee({{ $emp->id }})" class="text-blue-600 hover:text-blue-900 inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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

    @if($isModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col overflow-hidden">

            <div class="flex justify-between items-center px-8 py-5 border-b border-gray-100 flex-shrink-0 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isEditMode ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' }}"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ $isEditMode ? 'Edit Employee Details' : 'Register New Employee' }}
                        </h3>
                        <p class="text-xs text-gray-400">Step {{ $currentStep }} of 4</p>
                    </div>
                </div>
                <button wire:click="closeModal" type="button" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-8 py-4 bg-gray-50 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 right-0 top-4 h-0.5 bg-gray-200 z-0 mx-8"></div>
                    <div class="absolute left-0 top-4 h-0.5 bg-blue-500 z-0 mx-8 transition-all duration-300
                        {{ $currentStep == 1 ? 'w-0' : ($currentStep == 2 ? 'w-1/3' : ($currentStep == 3 ? 'w-2/3' : 'w-full')) }}"></div>

                    @foreach($steps as $stepNum => $step)
                    <button wire:click="setStep({{ $stepNum }})" type="button" class="flex flex-col items-center gap-1.5 z-10 group">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-200 shadow-sm
                            {{ $currentStep == $stepNum
                                ? 'bg-blue-600 border-blue-600 scale-110'
                                : ($currentStep > $stepNum ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300 group-hover:border-blue-400') }}">
                            @if($currentStep > $stepNum)
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @else
                            <svg class="w-3.5 h-3.5 {{ $currentStep == $stepNum ? 'text-white' : 'text-gray-400 group-hover:text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"></path>
                            </svg>
                            @endif
                        </div>
                        <span class="text-xs font-medium whitespace-nowrap
                            {{ $currentStep == $stepNum ? 'text-blue-600' : ($currentStep > $stepNum ? 'text-blue-400' : 'text-gray-400') }}">
                            {{ $step['label'] }}
                        </span>
                    </button>
                    @endforeach
                </div>
            </div>

            <form wire:submit.prevent="saveEmployee" class="flex-1 flex flex-col overflow-hidden">
                <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">

                    @if($currentStep == 1)
                    <div wire:key="step-1-personal-info-form-{{ $selectedEmployeeId }}">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Personal Information & System Access</h4>
                            <p class="text-xs text-gray-400 mb-5">Basic employee details and login permissions.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Employee ID *</label>
                                <input type="text" wire:model="employee_id" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('employee_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Phone Number *</label>
                                <input type="text" wire:model="phone_number" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('phone_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">First Name *</label>
                                <input type="text" wire:model="first_name" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Last Name *</label>
                                <input type="text" wire:model="last_name" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('last_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date of Birth *</label>
                                <input type="date" wire:model="date_of_birth" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('date_of_birth') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Gender *</label>
                                <select wire:model="gender" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('gender') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">NIC Number</label>
                                <input type="text" wire:model="nic_number" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Passport Number</label>
                                <input type="text" wire:model="passport_number" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                            </div>
                            <div class="relative">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Job Position *</label>
                                <div wire:click="$set('isDropdownOpen', {{ !$isDropdownOpen ? 'true' : 'false' }})" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-left bg-white flex justify-between items-center cursor-pointer focus:ring-1 focus:ring-blue-500">
                                    <span class="{{ $job_position ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ $job_position ? $job_position : 'Select Job Position' }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform {{ $isDropdownOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                @if($isDropdownOpen)
                                <select wire:model="job_position" wire:change="$set('isDropdownOpen', false)" size="3" class="absolute z-50 mt-1 w-full border border-gray-300 rounded-lg text-sm focus:outline-none bg-white shadow-lg overflow-y-auto">
                                    <option value="" class="p-2 hover:bg-gray-100 cursor-pointer text-gray-400">Select Job Position</option>
                                    @foreach($designations as $designation)
                                    <option value="{{ $designation->name }}" class="p-2 hover:bg-blue-50 hover:text-blue-600 cursor-pointer">
                                        {{ $designation->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @endif
                                @error('job_position') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Country *</label>
                                <input type="text" wire:model="country" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                @error('country') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Marital Status *</label>
                                <select wire:model="marital_status" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                    <option value="">Select Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                </select>
                                @error('marital_status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date of Join</label>
                                <input type="date" wire:model="date_of_join" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                            </div>

                            <div wire:key="email-status-container-{{ $selectedEmployeeId }}" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-2xl border border-blue-100 mt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">Login Email Address *</label>
                                    <input type="email"
                                        wire:model="email"
                                        wire:key="employee-login-email-input-{{ $selectedEmployeeId }}"
                                        placeholder="employee@company.com"
                                        class="mt-1 w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow">
                                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">Employee Status</label>
                                    <select wire:model="status" wire:key="employee-status-select-{{ $selectedEmployeeId }}" class="mt-1 w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow">
                                        <option value="Active"> Active</option>
                                        <option value="Inactive"> Inactive</option>
                                        <option value="Suspended"> Suspended</option>
                                    </select>
                                    @error('status') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($currentStep == 2)
                    <div wire:key="step-2-address-form-{{ $selectedEmployeeId }}">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Address & Emergency Contact</h4>
                            <p class="text-xs text-gray-400 mb-5">Residential address and emergency contact details.</p>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    </span>
                                    Residential Address
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Address Line 1 *</label>
                                        <input type="text" wire:model="address_line_1" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('address_line_1') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Address Line 2</label>
                                        <input type="text" wire:model="address_line_2" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">City *</label>
                                        <input type="text" wire:model="city" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('city') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Postal Code</label>
                                        <input type="text" wire:model="postal_code" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-red-50 rounded-2xl p-5 border border-red-100">
                                <h4 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </span>
                                    Emergency Contact Info
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Contact Name *</label>
                                        <input type="text" wire:model="contact_name" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('contact_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Relationship *</label>
                                        <input type="text" wire:model="relationship" placeholder="Ex: Father, Wife" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('relationship') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Emergency Phone *</label>
                                        <input type="text" wire:model="emergency_phone" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow hover:border-gray-300 bg-white">
                                        @error('emergency_phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($currentStep == 3)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Family Details</h4>
                        <p class="text-xs text-gray-400 mb-5">Spouse, Parent/Guardian information and children details.</p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                            <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </span>
                                @if($marital_status === 'Married')
                                Spouse Details
                                @else
                                Parent / Guardian Details
                                @endif
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">
                                        @if($marital_status === 'Married') Spouse Name @else Parent / Guardian Name @endif
                                    </label>
                                    <input type="text" wire:model="spouse_name" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Date of Birth</label>
                                    <input type="date" wire:model="spouse_dob" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">NIC Number</label>
                                    <input type="text" wire:model="spouse_nic" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Passport Number</label>
                                    <input type="text" wire:model="spouse_passport" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                            </div>
                        </div>

                        @if($marital_status === 'Married')
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </span>
                                Children Details
                            </h4>
                            <button type="button" wire:click="addChild" class="flex items-center gap-1.5 text-xs bg-blue-600 text-white font-medium py-1.5 px-3 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Child
                            </button>
                        </div>

                        @foreach($children as $index => $child)
                        <div wire:key="child-form-{{ $index }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 relative">
                            <div class="absolute -top-2 -left-2 w-6 h-6 bg-blue-600 text-white text-xs rounded-full flex items-center justify-center font-bold shadow">{{ $index + 1 }}</div>
                            <div>
                                <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Child Name</label>
                                <input type="text" wire:model="children.{{$index}}.name" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error("children.{$index}.name") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Birthday</label>
                                <input type="date" wire:model="children.{{$index}}.date_of_birth" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error("children.{$index}.date_of_birth") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Gender</label>
                                <select wire:model="children.{{$index}}.gender" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="flex items-end justify-between gap-2">
                                <div class="w-full">
                                    <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Marital Status</label>
                                    <select wire:model="children.{{$index}}.marital_status" class="mt-1 w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                    </select>
                                </div>
                                <button type="button" wire:click="removeChild({{ $index }})" class="w-8 h-9 flex items-center justify-center bg-red-50 text-red-500 border border-red-200 rounded-xl hover:bg-red-100 transition-colors flex-shrink-0 mb-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="flex items-center gap-3 p-4 bg-amber-50 text-amber-700 text-sm rounded-xl border border-amber-200">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Children registration is only available for <strong>Married</strong> employees.</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($currentStep == 4)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-1">Salary & Bank Details</h4>
                        <p class="text-xs text-gray-400 mb-5">Compensation structure and payment remittance information.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-5 border border-green-100">
                            <h4 class="text-xs font-bold text-green-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                                Salary Structure
                            </h4>
                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Basic Salary *</label>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">LKR</span>
                                    <input type="number" step="0.01" wire:model="basic_salary" class="w-full border border-green-200 bg-white rounded-xl pl-12 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-shadow font-semibold text-gray-800">
                                </div>
                                @error('basic_salary') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                            <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </span>
                                Bank Remittance
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Payment Method</label>
                                    <select wire:model="payment_method" class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                        <option value="Bank Transfer">🏦 Bank Transfer</option>
                                        <option value="Cash">💵 Cash</option>
                                        <option value="Cheque">📄 Cheque</option>
                                    </select>
                                    @error('payment_method') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account Holder</label>
                                    <input type="text" wire:model="account_holder_name" class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                </div>
                                <div class="relative">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Bank Name</label>
                                    <div class="relative mt-1">
                                        <input type="text" wire:model.live="searchBank" wire:click="$set('isBankDropdownOpen', true)" placeholder="Type to search bank..." class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-shadow">
                                        @if($bank_name)
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">✓ Set</span>
                                        @endif
                                    </div>
                                    @if($isBankDropdownOpen)
                                    <div class="absolute z-50 mt-1 w-full border border-gray-200 rounded-xl text-sm bg-white shadow-xl max-h-48 overflow-y-auto">
                                        @if($filteredBanks->isEmpty())
                                        <div class="p-3 text-gray-400 italic text-center">No banks found.</div>
                                        @else
                                        @foreach($filteredBanks as $bank)
                                        <div wire:click="selectBank('{{ $bank['bank_name'] }}', {{ $bank['id'] }})" class="px-3 py-2 hover:bg-blue-50 hover:text-blue-600 cursor-pointer border-b border-gray-50 last:border-none transition-colors">
                                            {{ $bank['bank_name'] }}
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                    @endif
                                    @error('bank_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="relative">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Branch Name</label>
                                    <div class="relative mt-1">
                                        <input type="text" wire:model.live="searchBranch" wire:click="$set('isBranchDropdownOpen', true)" placeholder="{{ $bank_name ? 'Type to search Branch...' : 'Select a bank first' }}" {{ $bank_name ? '' : 'disabled' }} class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400 transition-shadow">
                                        @if($branch_name)
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">✓ Set</span>
                                        @endif
                                    </div>
                                    @if($isBranchDropdownOpen && $bank_name)
                                    <div class="absolute z-50 mt-1 w-full border border-gray-200 rounded-xl text-sm bg-white shadow-xl max-h-48 overflow-y-auto">
                                        @if($filteredBranches->isEmpty())
                                        <div class="p-3 text-gray-400 italic text-center">No branches found for this bank.</div>
                                        @else
                                        @foreach($filteredBranches as $branch)
                                        <div wire:click="selectBranch('{{ $branch['branch_name'] }}')" class="px-3 py-2 hover:bg-blue-50 hover:text-blue-600 cursor-pointer border-b border-gray-50 last:border-none transition-colors">
                                            {{ $branch['branch_name'] }}
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                    @endif
                                    @error('branch_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account Number</label>
                                    <input type="text" wire:model="account_number" class="mt-1 w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                                    @error('account_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="px-8 py-4 bg-white border-t border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        @if($currentStep > 1)
                        <button wire:click="setStep({{ $currentStep - 1 }})" type="button" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-5 rounded-xl transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-400">{{ $currentStep }} / 4</span>
                        @if($currentStep < 4)
                            <button wire:click="setStep({{ $currentStep + 1 }})" type="button" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm">
                            Continue
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            </button>
                            @else
                            <button type="submit" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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