<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- ─── Flash Message ───────────────────────────────────── --}}
        @if (session('success'))
            <div
                class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Header ──────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Employees</h1>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Employee
            </button>
        </div>

        {{-- ─── Filters ─────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search employees…"
                    class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            </div>

            {{-- Status Filter --}}
            <div class="flex gap-0 border border-gray-300 rounded-lg overflow-hidden">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'all' => 'All'] as $val => $label)
                    <button type="button" wire:click="$set('filterStatus', '{{ $val }}')"
                        class="px-3 py-2 text-xs font-medium transition-colors
                            {{ $filterStatus === $val ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Department Filter --}}
            <select wire:model.live="filterDepartment"
                class="text-sm border border-gray-300 rounded-lg px-7 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- ─── Table ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Work Email
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Department
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs text-right font-semibold text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employeeList as $emp)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Employee Name + ID --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold uppercase flex-shrink-0">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <button type="button" wire:click="edit({{ $emp->id }})"
                                            class="text-blue-600 hover:text-blue-700 font-medium leading-tight">
                                            {{ $emp->name }}
                                        </button>
                                        @if (!$emp->is_profile_complete)
                                            <span class="ml-2 text-xs text-orange-500 font-medium">Incomplete</span>
                                        @endif
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $emp->designation?->name ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Work Email --}}
                            <td class="px-6 py-4 text-gray-600">{{ $emp->work_email ?? '—' }}</td>

                            {{-- Department --}}
                            <td class="px-6 py-4 text-gray-600">{{ $emp->department?->name ?? '—' }}</td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if (!$emp->is_profile_complete)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Incomplete
                                    </span>
                                @elseif($emp->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <button type="button" wire:click="edit({{ $emp->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $emp->id }})"
                                        class="text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($employeeList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $employeeList->links() }}
                </div>
            @endif
        </div>


        {{-- ═══════════════════════════════════════════════════════
             ADD / EDIT EMPLOYEE — 4-Step Modal
        ════════════════════════════════════════════════════════ --}}
        @if ($showModal)
            <div class="fixed inset-y-0 left-64 right-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data
                x-on:keydown.escape.window="$wire.closeModal()">

                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl flex flex-col overflow-hidden"
                    style="height: 88vh;">

                    {{-- ── Modal Header ──────────────────────────── --}}
                    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-200 flex-shrink-0">
                        <h2 class="text-lg font-semibold text-gray-800">
                            {{ $editingId ? 'Edit Employee' : 'Add Employee' }}
                        </h2>
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- ── Step Indicator ────────────────────────── --}}
                    <div class="px-8 py-4 border-b border-gray-100 flex-shrink-0">
                        <div class="relative flex items-start">

                            {{-- Background connector line: first circle center to last circle center --}}
                            <div class="absolute h-0.5 bg-gray-200 z-0" style="top: 16px; left: 12.5%; right: 12.5%;">
                            </div>

                            {{-- Foreground blue progress line --}}
                            <div class="absolute h-0.5 bg-gray-300 z-0 transition-all duration-500"
                                style="top: 16px;
                   left: 12.5%;
                   width: calc({{ $currentStep - 1 }} * 25%);
                   max-width: 75%;">
                            </div>

                            @foreach ([1 => 'Basic Details', 2 => 'Salary Details', 3 => 'Personal Details', 4 => 'Payment Information'] as $step => $label)
                                <div class="flex flex-col items-center gap-1.5 z-10 relative" style="width: 25%;">
                                    <button type="button"
                                        @if ($step < $currentStep) wire:click="goToStep({{ $step }})" @endif
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all border-2
                        {{ $currentStep > $step
                            ? 'bg-green-500 border-green-500 text-white cursor-pointer hover:bg-green-600'
                            : ($currentStep === $step
                                ? 'bg-blue-600 border-blue-600 text-white cursor-default'
                                : 'bg-white border-gray-300 text-gray-400 cursor-default') }}">
                                        @if ($currentStep > $step)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            {{ $step }}
                                        @endif
                                    </button>
                                    <span
                                        class="text-xs font-medium whitespace-nowrap
                    {{ $currentStep > $step ? 'text-gray-600' : ($currentStep === $step ? 'text-blue-600' : 'text-gray-400') }}">
                                        {{ $label }}
                                    </span>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    {{-- ── Scrollable Body ───────────────────────── --}}
                    <div class="flex-1 overflow-y-auto px-8 py-6">

                        {{-- ══════════════ STEP 1: Basic Details ══════════════ --}}
                        @if ($currentStep === 1)
                            <div class="space-y-5">
                                {{-- Employee Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Employee Name <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <input type="text" wire:model="firstName" placeholder="First Name"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('firstName') border-red-400 @enderror">
                                            @error('firstName')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <input type="text" wire:model="middleName" placeholder="Middle Name"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <input type="text" wire:model="lastName" placeholder="Last Name"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                    </div>
                                </div>

                                {{-- Employee ID + Date of Joining --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Employee ID <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="employeeId"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('employeeId') border-red-400 @enderror">
                                        @error('employeeId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Date of Joining <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" wire:model="dateOfJoining"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dateOfJoining') border-red-400 @enderror">
                                        @error('dateOfJoining')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Work Email + Mobile --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Work Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" wire:model="workEmail" placeholder="abc@xyz.com"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('workEmail') border-red-400 @enderror">
                                        @error('workEmail')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile
                                            Number</label>
                                        <input type="text" wire:model="mobileNumber"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>

                                {{-- Director checkbox --}}
                                <div class="flex items-start gap-2.5">
                                    <input type="checkbox" id="isDirector" wire:model="isDirector"
                                        class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                    <label for="isDirector" class="text-sm text-gray-700 cursor-pointer">
                                        Employee is a <strong>Director/person with substantial interest</strong> in the
                                        company.
                                    </label>
                                </div>

                                {{-- Gender + Work Location --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Gender <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="gender"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('gender') border-red-400 @enderror">
                                            <option value="">Select</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('gender')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Work Location <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="workLocationId"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('workLocationId') border-red-400 @enderror">
                                            <option value="">Select</option>
                                            @foreach ($workLocations as $loc)
                                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('workLocationId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Designation + Department --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Designation <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="designationId"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('designationId') border-red-400 @enderror">
                                            <option value="">Select</option>
                                            @foreach ($designations as $des)
                                                <option value="{{ $des->id }}">{{ $des->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('designationId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Department <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="departmentId"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('departmentId') border-red-400 @enderror">
                                            <option value="">Select</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('departmentId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Enable Portal --}}
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start gap-2.5">
                                        <input type="checkbox" id="enablePortal" wire:model="enablePortal"
                                            class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                        <div>
                                            <label for="enablePortal"
                                                class="text-sm font-medium text-gray-700 cursor-pointer">Enable Portal
                                                Access</label>
                                            <p class="text-xs text-gray-500 mt-0.5">The employee will be able to view
                                                payslips, submit their IT declaration and create reimbursement claims
                                                through the employee portal.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Info banner --}}
                                <div
                                    class="flex items-start gap-2 bg-blue-50 border border-blue-100 text-blue-700 text-xs px-4 py-3 rounded-lg">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span><strong>Statutory components</strong> are now part of Salary Details, ensuring
                                        all salary-linked information is captured and reviewed in one place.</span>
                                </div>
                            </div>
                        @endif

                        {{-- ══════════════ STEP 2: Salary Details ══════════════ --}}
                        @if ($currentStep === 2 && !$showSalarySummary)
                            <div class="space-y-5">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800">Salary Structure</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">Set how the employee's salary is divided
                                        for accurate pay calculation.</p>
                                </div>

                                {{-- Basic Monthly Salary --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Basic Monthly Salary <span class="text-red-500">*</span>
                                    </label>
                                    <div
                                        class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent w-72">
                                        @if ($baseCurrency)
                                            <span
                                                class="inline-flex items-center px-3 py-2 bg-gray-100 border-r border-gray-300 text-sm text-gray-600 font-medium select-none">
                                                {{ $baseCurrency->code }}
                                            </span>
                                        @endif
                                        <input type="number" wire:model.live="basicMonthlyAmount" step="0.01"
                                            min="0"
                                            class="flex-1 px-3 py-2 text-sm focus:outline-none border-0"
                                            placeholder="0.00">
                                        <span
                                            class="inline-flex items-center px-3 py-2 bg-gray-50 border-l border-gray-300 text-xs text-gray-500 select-none">per
                                            month</span>
                                    </div>
                                    @error('basicMonthlyAmount')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Earnings Table --}}
                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-gray-50 border-b border-gray-200">
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Earnings</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Calculation</th>
                                                <th
                                                    class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Monthly Amount</th>
                                                <th
                                                    class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Annual Amount</th>
                                                <th
                                                    class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            {{-- Basic Row --}}
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-5 py-3">
                                                    <div class="font-medium text-gray-800">Basic</div>
                                                    <div class="text-xs text-gray-400">Base salary component</div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <span class="text-sm text-gray-600">Fixed amount</span>
                                                </td>
                                                <td class="px-5 py-3 text-right font-medium text-gray-800">
                                                    {{ $baseCurrency?->code }}
                                                    {{ number_format($basicMonthlyAmount ?? 0, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-right text-gray-700">
                                                    {{ $baseCurrency?->code }}
                                                    {{ number_format(($basicMonthlyAmount ?? 0) * 12, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-center"></td>
                                            </tr>

                                            {{-- Added Earnings Rows --}}
                                            @foreach ($addedEarnings as $index => $earning)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-5 py-3">
                                                        <div class="font-medium text-gray-800">
                                                            {{ $earning['component_name'] }}</div>
                                                        <div class="text-xs text-gray-400">
                                                            {{ $earning['name_in_payslip'] }}</div>
                                                    </td>
                                                    <td class="px-5 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex gap-3">
                                                                <label
                                                                    class="flex items-center gap-1.5 cursor-pointer">
                                                                    <input type="radio"
                                                                        name="earning_type_{{ $index }}"
                                                                        wire:change="updateEarningCalcType({{ $index }}, 'percentage')"
                                                                        {{ $earning['calculation_type'] === 'percentage' ? 'checked' : '' }}
                                                                        class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-600">%</span>
                                                                </label>
                                                                <label
                                                                    class="flex items-center gap-1.5 cursor-pointer">
                                                                    <input type="radio"
                                                                        name="earning_type_{{ $index }}"
                                                                        wire:change="updateEarningCalcType({{ $index }}, 'fixed')"
                                                                        {{ $earning['calculation_type'] === 'fixed' ? 'checked' : '' }}
                                                                        class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-600">Fixed</span>
                                                                </label>
                                                            </div>
                                                            @if ($earning['calculation_type'] === 'percentage')
                                                                <div class="flex items-center gap-1">
                                                                    <input type="number" step="0.01"
                                                                        min="0" max="100"
                                                                        wire:change="updateEarningValue({{ $index }}, 'percentage_value', $event.target.value)"
                                                                        value="{{ $earning['percentage_value'] }}"
                                                                        class="w-16 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-500">% of
                                                                        Basic</span>
                                                                </div>
                                                            @else
                                                                <div class="flex items-center gap-1">
                                                                    <span
                                                                        class="text-xs text-gray-500">{{ $baseCurrency?->code }}</span>
                                                                    <input type="number" step="0.01"
                                                                        min="0"
                                                                        wire:change="updateEarningValue({{ $index }}, 'fixed_amount', $event.target.value)"
                                                                        value="{{ $earning['fixed_amount'] }}"
                                                                        class="w-28 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-500">/month</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 text-right text-gray-700">
                                                        {{ $baseCurrency?->code }}
                                                        {{ number_format($earning['monthly_amount'], 2) }}
                                                    </td>
                                                    <td class="px-5 py-3 text-right text-gray-700">
                                                        {{ $baseCurrency?->code }}
                                                        {{ number_format($earning['annual_amount'], 2) }}
                                                    </td>
                                                    <td class="px-5 py-3 text-center">
                                                        <button type="button"
                                                            wire:click="removeEarning({{ $index }})"
                                                            class="text-red-500 hover:text-red-700 transition-colors"
                                                            title="Remove">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    {{-- Add Earning Button --}}
                                    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">
                                        <button type="button" wire:click="openEarningModal"
                                            class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Earning
                                        </button>
                                    </div>
                                </div>

                                {{-- Deductions Table --}}
                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-gray-50 border-b border-gray-200">
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Deductions</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Calculation</th>
                                                <th
                                                    class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Monthly Amount</th>
                                                <th
                                                    class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    Annual Amount</th>
                                                <th
                                                    class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse($addedDeductions as $index => $deduction)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-5 py-3">
                                                        <div class="font-medium text-gray-800">
                                                            {{ $deduction['component_name'] }}</div>
                                                        <div class="text-xs text-gray-400">
                                                            {{ $deduction['name_in_payslip'] }}</div>
                                                    </td>
                                                    <td class="px-5 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex gap-3">
                                                                <label
                                                                    class="flex items-center gap-1.5 cursor-pointer">
                                                                    <input type="radio"
                                                                        name="deduction_type_{{ $index }}"
                                                                        wire:change="updateDeductionCalcType({{ $index }}, 'percentage')"
                                                                        {{ $deduction['calculation_type'] === 'percentage' ? 'checked' : '' }}
                                                                        class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-600">%</span>
                                                                </label>
                                                                <label
                                                                    class="flex items-center gap-1.5 cursor-pointer">
                                                                    <input type="radio"
                                                                        name="deduction_type_{{ $index }}"
                                                                        wire:change="updateDeductionCalcType({{ $index }}, 'fixed')"
                                                                        {{ $deduction['calculation_type'] === 'fixed' ? 'checked' : '' }}
                                                                        class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-600">Fixed</span>
                                                                </label>
                                                            </div>
                                                            @if ($deduction['calculation_type'] === 'percentage')
                                                                <div class="flex items-center gap-1">
                                                                    <input type="number" step="0.01"
                                                                        min="0" max="100"
                                                                        wire:change="updateDeductionValue({{ $index }}, 'percentage_value', $event.target.value)"
                                                                        value="{{ $deduction['percentage_value'] }}"
                                                                        class="w-16 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-500">% of
                                                                        Basic</span>
                                                                </div>
                                                            @else
                                                                <div class="flex items-center gap-1">
                                                                    <span
                                                                        class="text-xs text-gray-500">{{ $baseCurrency?->code }}</span>
                                                                    <input type="number" step="0.01"
                                                                        min="0"
                                                                        wire:change="updateDeductionValue({{ $index }}, 'fixed_amount', $event.target.value)"
                                                                        value="{{ $deduction['fixed_amount'] }}"
                                                                        class="w-28 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                    <span class="text-xs text-gray-500">/month</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-3 text-right text-gray-700">
                                                        {{ $baseCurrency?->code }}
                                                        {{ number_format($deduction['monthly_amount'], 2) }}
                                                    </td>
                                                    <td class="px-5 py-3 text-right text-gray-700">
                                                        {{ $baseCurrency?->code }}
                                                        {{ number_format($deduction['annual_amount'], 2) }}
                                                    </td>
                                                    <td class="px-5 py-3 text-center">
                                                        <button type="button"
                                                            wire:click="removeDeduction({{ $index }})"
                                                            class="text-red-500 hover:text-red-700 transition-colors"
                                                            title="Remove">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5"
                                                        class="px-5 py-8 text-center text-gray-400 text-sm">
                                                        No deductions added yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    {{-- Add Deduction Button --}}
                                    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">
                                        <button type="button" wire:click="openDeductionModal"
                                            class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Deduction
                                        </button>
                                    </div>
                                </div>

                                {{-- ── Live Salary Summary Bar ─────────────────────────── --}}
                                <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                                    <div class="px-5 py-3 border-b border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-700">Salary Summary</h4>
                                    </div>
                                    <div class="px-5 py-3 space-y-2">
                                        {{-- Total Earnings --}}
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-500">Total Earnings (Monthly)</span>
                                            <span class="font-medium text-gray-800">
                                                {{ $baseCurrency?->code }}
                                                {{ number_format($this->totalMonthlyEarnings, 2) }}
                                            </span>
                                        </div>

                                        {{-- Total Deductions --}}
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-500">Total Deductions (Monthly)</span>
                                            <span class="font-medium text-red-600">
                                                - {{ $baseCurrency?->code }}
                                                {{ number_format($this->totalMonthlyDeductions, 2) }}
                                            </span>
                                        </div>

                                        {{-- Divider --}}
                                        <div class="border-t border-gray-200 pt-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-semibold text-gray-800">Net Salary
                                                    (Monthly)</span>
                                                <span class="text-base font-bold text-blue-600">
                                                    {{ $baseCurrency?->code }}
                                                    {{ number_format($this->netMonthlySalary, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ══════════════ STEP 2: Salary Summary (After Save & Continue) ══════════════ --}}
                        @if ($currentStep === 2 && $showSalarySummary)
                            <div class="space-y-5">
                                {{-- Header with Edit Button --}}
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-800">Salary Details Summary</h3>
                                        <p class="text-sm text-gray-500 mt-0.5">Review the salary structure before
                                            proceeding.</p>
                                    </div>
                                    <button type="button" wire:click="editSalaryFromSummary"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                </div>

                                {{-- Basic Salary Card --}}
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-500">Basic Monthly Salary</p>
                                            <p class="text-xl font-semibold text-gray-800">{{ $baseCurrency?->code }}
                                                {{ number_format($basicMonthlyAmount ?? 0, 2) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Basic Annual Salary</p>
                                            <p class="text-lg font-medium text-gray-700">{{ $baseCurrency?->code }}
                                                {{ number_format(($basicMonthlyAmount ?? 0) * 12, 2) }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Earnings Summary Table --}}
                                @if (count($addedEarnings) > 0)
                                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-200">
                                                    <th
                                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Earnings</th>
                                                    <th
                                                        class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Monthly Amount</th>
                                                    <th
                                                        class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Annual Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($addedEarnings as $earning)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-5 py-3">
                                                            <div class="font-medium text-gray-800">
                                                                {{ $earning['component_name'] }}</div>
                                                            <div class="text-xs text-gray-400">
                                                                @if ($earning['calculation_type'] === 'percentage')
                                                                    {{ $earning['percentage_value'] }}% of Basic
                                                                @else
                                                                    Fixed: {{ $baseCurrency?->code }}
                                                                    {{ number_format($earning['fixed_amount'], 2) }}/month
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-3 text-right text-gray-700">
                                                            {{ $baseCurrency?->code }}
                                                            {{ number_format($earning['monthly_amount'], 2) }}
                                                        </td>
                                                        <td class="px-5 py-3 text-right text-gray-700">
                                                            {{ $baseCurrency?->code }}
                                                            {{ number_format($earning['annual_amount'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- Deductions Summary Table --}}
                                @if (count($addedDeductions) > 0)
                                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-50 border-b border-gray-200">
                                                    <th
                                                        class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Deductions</th>
                                                    <th
                                                        class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Monthly Amount</th>
                                                    <th
                                                        class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                        Annual Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($addedDeductions as $deduction)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-5 py-3">
                                                            <div class="font-medium text-gray-800">
                                                                {{ $deduction['component_name'] }}</div>
                                                            <div class="text-xs text-gray-400">
                                                                @if ($deduction['calculation_type'] === 'percentage')
                                                                    {{ $deduction['percentage_value'] }}% of Basic
                                                                @else
                                                                    Fixed: {{ $baseCurrency?->code }}
                                                                    {{ number_format($deduction['fixed_amount'], 2) }}/month
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-3 text-right text-gray-700">
                                                            {{ $baseCurrency?->code }}
                                                            {{ number_format($deduction['monthly_amount'], 2) }}
                                                        </td>
                                                        <td class="px-5 py-3 text-right text-gray-700">
                                                            {{ $baseCurrency?->code }}
                                                            {{ number_format($deduction['annual_amount'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                {{-- Final Summary Card --}}
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Salary Summary</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Earnings (Monthly)</span>
                                            <span class="font-medium text-gray-800">{{ $baseCurrency?->code }}
                                                {{ number_format($this->total_monthly_earnings, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Deductions (Monthly)</span>
                                            <span class="font-medium text-red-600">- {{ $baseCurrency?->code }}
                                                {{ number_format($this->total_monthly_deductions, 2) }}</span>
                                        </div>
                                        <div class="border-t border-gray-200 my-2"></div>
                                        <div class="flex justify-between text-base font-semibold">
                                            <span class="text-gray-800">Net Salary (Monthly)</span>
                                            <span class="text-blue-600">{{ $baseCurrency?->code }}
                                                {{ number_format($this->net_monthly_salary, 2) }}</span>
                                        </div>
                                        <div class="border-t border-gray-200 my-2"></div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Earnings (Annual)</span>
                                            <span class="font-medium text-gray-800">{{ $baseCurrency?->code }}
                                                {{ number_format($this->total_annual_earnings, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Deductions (Annual)</span>
                                            <span class="font-medium text-red-600">- {{ $baseCurrency?->code }}
                                                {{ number_format($this->total_annual_deductions, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-base font-semibold pt-1">
                                            <span class="text-gray-800">Net Salary (Annual)</span>
                                            <span class="text-blue-600">{{ $baseCurrency?->code }}
                                                {{ number_format($this->net_annual_salary, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Add Earning Modal --}}
                        @if ($showEarningForm && $currentStep === 2)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                wire:keydown.escape="closeEarningModal">
                                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                        <h2 class="text-base font-semibold text-gray-800">Add Earning Component</h2>
                                        <button type="button" wire:click="closeEarningModal"
                                            class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-6 py-5 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Component <span class="text-red-500">*</span>
                                            </label>

                                            {{-- Search Input --}}
                                            <div class="relative">
                                                <div class="relative">
                                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                                                    </svg>
                                                    <input type="text"
                                                        wire:model.live.debounce.200ms="earningSearch"
                                                        wire:focus="$set('showEarningDropdown', true)"
                                                        placeholder="Search earning component..." autocomplete="off"
                                                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       @error('selectedEarningComponent') border-red-400 @enderror">
                                                    {{-- Clear button --}}
                                                    @if ($earningSearch)
                                                        <button type="button"
                                                            wire:click="$set('earningSearch', ''); $set('selectedEarningComponent', ''); $set('showEarningDropdown', false)"
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>

                                                {{-- Dropdown Results --}}
                                                @if ($showEarningDropdown)
                                                    <div
                                                        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                                        @forelse ($this->filteredEarningComponents as $comp)
                                                            <button type="button"
                                                                wire:click="selectEarningComponent({{ $comp->id }})"
                                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors
                               {{ $selectedEarningComponent == $comp->id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                                                                <span class="font-medium">{{ $comp->name }}</span>
                                                                <span
                                                                    class="text-gray-400 text-xs ml-1">({{ $comp->name_in_payslip }})</span>
                                                            </button>
                                                        @empty
                                                            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                                                                No components found.
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>

                                            @error('selectedEarningComponent')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Calculation
                                                Type</label>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model.live="tempCalcType"
                                                        value="percentage"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Percentage (%) of Basic</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model.live="tempCalcType"
                                                        value="fixed"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Fixed Amount (Monthly)</span>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($tempCalcType === 'percentage')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Percentage
                                                    (%)</label>
                                                <input type="number" step="0.01" min="0" max="100"
                                                    wire:model="tempPercentage"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="e.g., 50 for 50%">
                                            </div>
                                        @else
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Annual
                                                    Amount</label>
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        class="text-sm text-gray-500">{{ $baseCurrency?->code }}</span>
                                                    <input type="number" step="0.01" min="0"
                                                        wire:model="tempFixedAmount"
                                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div
                                        class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                                        <span class="text-xs text-red-500">* indicates mandatory fields</span>
                                        <div class="flex gap-3">
                                            <button type="button" wire:click="closeEarningModal"
                                                class="border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="button" wire:click="addEarning"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                Add Earning
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Add Deduction Modal --}}
                        @if ($showDeductionForm && $currentStep === 2)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                wire:keydown.escape="closeDeductionModal">
                                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                        <h2 class="text-base font-semibold text-gray-800">Add Deduction Component</h2>
                                        <button type="button" wire:click="closeDeductionModal"
                                            class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-6 py-5 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Component <span class="text-red-500">*</span>
                                            </label>

                                            <div class="relative">
                                                <div class="relative">
                                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                                                    </svg>
                                                    <input type="text"
                                                        wire:model.live.debounce.200ms="deductionSearch"
                                                        wire:focus="$set('showDeductionDropdown', true)"
                                                        placeholder="Search deduction component..." autocomplete="off"
                                                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       @error('selectedDeductionComponent') border-red-400 @enderror">
                                                    @if ($deductionSearch)
                                                        <button type="button"
                                                            wire:click="$set('deductionSearch', ''); $set('selectedDeductionComponent', ''); $set('showDeductionDropdown', false)"
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>

                                                @if ($showDeductionDropdown)
                                                    <div
                                                        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                                        @forelse ($this->filteredDeductionComponents as $comp)
                                                            <button type="button"
                                                                wire:click="selectDeductionComponent({{ $comp->id }})"
                                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors
                               {{ $selectedDeductionComponent == $comp->id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                                                                <span class="font-medium">{{ $comp->name }}</span>
                                                                <span
                                                                    class="text-gray-400 text-xs ml-1">({{ $comp->name_in_payslip }})</span>
                                                            </button>
                                                        @empty
                                                            <div class="px-4 py-3 text-sm text-gray-400 text-center">
                                                                No components found.
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>

                                            @error('selectedDeductionComponent')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Calculation
                                                Type</label>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model.live="tempCalcType"
                                                        value="percentage"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Percentage (%) of Basic</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model.live="tempCalcType"
                                                        value="fixed"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Fixed Amount (Monthly)</span>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($tempCalcType === 'percentage')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Percentage
                                                    (%)</label>
                                                <input type="number" step="0.01" min="0" max="100"
                                                    wire:model="tempPercentage"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="e.g., 12 for 12%">
                                            </div>
                                        @else
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Annual
                                                    Amount</label>
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        class="text-sm text-gray-500">{{ $baseCurrency?->code }}</span>
                                                    <input type="number" step="0.01" min="0"
                                                        wire:model="tempFixedAmount"
                                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div
                                        class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                                        <span class="text-xs text-red-500">* indicates mandatory fields</span>
                                        <div class="flex gap-3">
                                            <button type="button" wire:click="closeDeductionModal"
                                                class="border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="button" wire:click="addDeduction"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                                Add Deduction
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ══════════════ STEP 3: Personal Details ══════════════ --}}
                        @if ($currentStep === 3)
                            <div class="space-y-5">
                                {{-- Date of Birth + Age --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Date of Birth <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" wire:model="dateOfBirth"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dateOfBirth') border-red-400 @enderror">
                                        @error('dateOfBirth')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Age</label>
                                        <input type="text" readonly
                                            value="{{ $dateOfBirth ? \Carbon\Carbon::parse($dateOfBirth)->age . ' years' : '' }}"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                                    </div>
                                </div>

                                {{-- Father's Name + PAN --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Parent / Other Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="parentName"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('parentName') border-red-400 @enderror">
                                        @error('parentName')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Emergency Contact
                                            Number</label>
                                        <input type="text" wire:model="emergencyContactNumber"
                                            placeholder="07XXXXXXXX" maxlength="10"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase @error('pan') border-red-400 @enderror">
                                        @error('emergencyContactNumber')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Differently Abled + Personal Email --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Differently Abled
                                            Type</label>
                                        <select wire:model="differentlyAbledType"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">None</option>
                                            <option value="Visual">Visual</option>
                                            <option value="Hearing">Hearing</option>
                                            <option value="Locomotor">Locomotor</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Personal Email
                                            Address</label>
                                        <input type="email" wire:model="personalEmail"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('personalEmail') border-red-400 @enderror">
                                        @error('personalEmail')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Residential Address --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Residential
                                        Address</label>
                                    <div class="space-y-2">
                                        <input type="text" wire:model="residentialAddress1"
                                            placeholder="Address Line 1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <input type="text" wire:model="residentialAddress2"
                                            placeholder="Address Line 2"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent ">
                                        <div class="grid grid-cols-3 gap-2">
                                            <input type="text" wire:model="residentialCity" placeholder="City"
                                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <select wire:model="residentialState"
                                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">State</option>
                                                @foreach (['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi', 'Jammu & Kashmir', 'Ladakh', 'Puducherry'] as $state)
                                                    <option value="{{ $state }}">{{ $state }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" wire:model="residentialPincode"
                                                placeholder="Pincode" maxlength="10"
                                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-blue-50/30">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ══════════════ STEP 4: Payment Information ══════════════ --}}
                        @if ($currentStep === 4)
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700">
                                        How would you like to pay this employee? <span class="text-red-500">*</span>
                                    </h3>
                                    <div class="mt-3 space-y-2">
                                        {{-- Bank Transfer option --}}
                                        <label
                                            class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all
                                            {{ $paymentMethod === 'bank_transfer' ? 'border-blue-500 bg-blue-50/40' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                                            <input type="radio" wire:model="paymentMethod" value="bank_transfer"
                                                class="sr-only">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-gray-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-gray-800">Bank Transfer (Manual
                                                    Process)</div>
                                                <div class="text-xs text-gray-500 mt-0.5">Download Bank Advice and
                                                    process the payment through your bank's website</div>
                                            </div>
                                            <div
                                                class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5
                                                {{ $paymentMethod === 'bank_transfer' ? 'border-blue-500 bg-blue-500' : 'border-gray-300' }}">
                                                @if ($paymentMethod === 'bank_transfer')
                                                    <svg class="w-3 h-3 text-white" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Bank Details --}}
                                <div class="space-y-4">
                                    {{-- Account Holder Name --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Account Holder Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="accountHolderName"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('accountHolderName') border-red-400 @enderror">
                                        @error('accountHolderName')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Bank (Searchable) --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Bank <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="relative">
                                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                                                </svg>
                                                <input type="text" wire:model.live.debounce.200ms="bankSearch"
                                                    @if(!$selectedBankId) wire:focus="openBankDropdown" wire:blur="closeBankDropdown" @endif placeholder="Search bank..."
                                                    autocomplete="off"
                                                    @if($selectedBankId) readonly @endif
                                                    class="w-full pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    {{ $selectedBankId ? 'bg-gray-50 cursor-default' : '' }}
                    @error('selectedBankId') border-red-400 @enderror">
                                                @if ($selectedBankId)
                                                    <button type="button"
                                                        wire:click="clearBank"
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                            @if ($showBankDropdown)
                                                <div
                                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto"
                                                    x-data x-on:click.outside="$wire.set('showBankDropdown', false)">
                                                    @forelse ($this->filteredBanks as $bank)
                                                        <button type="button"
                                                            wire:mousedown.prevent wire:click="selectBank({{ $bank->id }})"
                                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors
                            {{ $selectedBankId == $bank->id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                                                            <span
                                                                class="font-mono text-xs text-gray-400 mr-1.5">{{ $bank->bank_code }}</span>
                                                            <span class="font-medium">{{ $bank->bank_name }}</span>
                                                        </button>
                                                    @empty
                                                        <div class="px-4 py-3 text-sm text-gray-400 text-center">No
                                                            banks found.</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('selectedBankId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Branch (Searchable — enabled only after bank selected) --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Branch <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="relative">
                                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <input type="text" wire:model.live.debounce.200ms="branchSearch"
                                                    @if(!$selectedBranchId && $selectedBankId) wire:focus="openBranchDropdown" wire:blur="closeBranchDropdown" @endif
                                                    placeholder="{{ $selectedBranchId ? '' : ($selectedBankId ? 'Search branch...' : 'Select a bank first') }}"
                                                    autocomplete="off"
                                                    @if (!$selectedBankId) disabled @endif
                                                    @if ($selectedBranchId) readonly @endif
                                                    class="w-full pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    {{ !$selectedBankId ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : ($selectedBranchId ? 'bg-gray-50 cursor-default' : '') }}
                    @error('selectedBranchId') border-red-400 @enderror">
                                                @if ($selectedBranchId)
                                                    <button type="button"
                                                        wire:click="clearBranch"
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                            @if ($showBranchDropdown && $selectedBankId)
                                                <div
                                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                                    @forelse ($this->filteredBranches as $branch)
                                                        <button type="button"
                                                            wire:mousedown.prevent wire:click="selectBranch({{ $branch->id }})"
                                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors
                            {{ $selectedBranchId == $branch->id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700' }}">
                                                            <span
                                                                class="font-mono text-xs text-gray-400 mr-1.5">{{ $branch->branch_code }}</span>
                                                            <span
                                                                class="font-medium">{{ $branch->branch_name }}</span>
                                                        </button>
                                                    @empty
                                                        <div class="px-4 py-3 text-sm text-gray-400 text-center">No
                                                            branches found.</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('selectedBranchId')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Account Number + Re-enter --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Account Number <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model="accountNumber"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('accountNumber') border-red-400 @enderror">
                                            @error('accountNumber')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        {{-- <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Re-enter Account Number <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model="confirmAccountNumber"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('confirmAccountNumber') border-red-400 @enderror">
                                            @error('confirmAccountNumber')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div> --}}
                                    </div>

                                    {{-- IFSC + Account Type --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Account Type <span class="text-red-500">*</span>
                                            </label>
                                            <div class="flex items-center gap-6 mt-2">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model="accountType" value="current"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Current</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" wire:model="accountType" value="savings"
                                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-sm text-gray-700">Savings</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>{{-- end scrollable body --}}

                    {{-- Modal Footer --}}
                    <div
                        class="flex items-center justify-between px-8 py-5 border-t border-gray-200 bg-gray-50 flex-shrink-0">
                        <span class="text-xs text-red-500">* indicates mandatory fields</span>

                        <div class="flex items-center gap-3">
                            @if ($currentStep === 1)
                                <button type="button" wire:click="closeModal"
                                    class="border border-gray-300 text-gray-700 text-sm font-medium px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    Cancel
                                </button>
                            @elseif($currentStep === 2 && $showSalarySummary)
                                <button type="button" wire:click="goBackToStep2"
                                    class="border border-gray-300 text-gray-700 text-sm font-medium px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                            @elseif($currentStep > 1 && !($currentStep === 2 && $showSalarySummary))
                                <button type="button" wire:click="prevStep"
                                    class="border border-gray-300 text-gray-700 text-sm font-medium px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                            @endif

                            @if ($currentStep < 4)
                                @if ($currentStep === 2 && $showSalarySummary)
                                    <button type="button" wire:click="confirmAndContinue"
                                        wire:loading.attr="disabled"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition-colors disabled:opacity-60 inline-flex items-center gap-1.5">
                                        <span wire:loading wire:target="confirmAndContinue">
                                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8H4z" />
                                            </svg>
                                        </span>
                                        Continue to Personal Details
                                        <svg wire:loading.remove wire:target="confirmAndContinue" class="w-4 h-4"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition-colors disabled:opacity-60 inline-flex items-center gap-1.5">
                                        <span wire:loading wire:target="nextStep">
                                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8H4z" />
                                            </svg>
                                        </span>
                                        Save and Continue
                                        <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @endif
                            @else
                                <button type="button" wire:click="save" wire:loading.attr="disabled"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-8 py-2 rounded-lg transition-colors disabled:opacity-60 inline-flex items-center gap-2">
                                    <span wire:loading wire:target="save">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                        </svg>
                                    </span>
                                    Save Employee
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ─── Delete Confirm Modal ──────────────────────────────── --}}
        @if ($confirmDeleteId)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-md mx-4 p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800">Delete Employee?</h3>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 ml-13">This action cannot be undone. All employee data will be
                        permanently deleted.</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDelete"
                            class="border border-gray-300 text-gray-700 text-sm px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete" wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-5 py-2 rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
