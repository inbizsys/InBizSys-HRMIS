<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Unified Header Section (Search, Category Filter & Bulk Action in One Row) -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Salary Structure & Component Assignments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage ongoing monthly allowances or assign single-month bonuses individually and in bulk.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="w-full sm:w-56">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or EMP ID..."
                    class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            {{-- Category Filter Dropdown --}}
            <div class="w-full sm:w-48">
                <select wire:model.live="selectedCategoryFilter"
                    class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- All Categories --</option>
                    @foreach ($categories as $categoryItem)
                        <option value="{{ $categoryItem->id }}">{{ $categoryItem->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month Filter (Default: Current Month) --}}
            <div class="w-full sm:w-40">
                <input type="month" wire:model.live="selectedMonthFilter" title="Select Month for Allowances & Deductions"
                    class="w-full text-xs font-semibold border border-blue-200 rounded-xl px-3 py-2.5 bg-blue-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none text-blue-900">
            </div>

            {{-- Bulk Assign Button --}}
            <button type="button" wire:click="openBulkModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition gap-1.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                + Bulk Assign
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('successMessage'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('successMessage') }}
        </div>
    @endif
    @if (session()->has('errorMessage'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
            {{ session('errorMessage') }}
        </div>
    @endif

    <!-- Employees Data Table -->
    <div class="w-full bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Basic Salary</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Components Count</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Allowances <span class="text-blue-600 font-mono font-bold">({{ $selectedMonthFilter }})</span>
                        </th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Deductions <span class="text-rose-600 font-mono font-bold">({{ $selectedMonthFilter }})</span>
                        </th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                <div class="text-xs font-mono font-semibold text-slate-500">
                                    {{ $employee->employee_id }} &bull; {{ $employee->category?->name ?? 'Unassigned' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-800">
                                LKR {{ number_format($employee->salaryAndBank?->basic_salary ?? 0.00, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <span class="px-2.5 py-1 rounded-full font-bold {{ $employee->salaryComponents->count() > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $employee->salaryComponents->count() }} Components
                                </span>
                            </td>
                            {{-- Calculated Rupee Values (Percentage included) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-emerald-600">
                                LKR {{ number_format($this->calculateTotalAmountForEmployee($employee, 'earning'), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-rose-600">
                                LKR {{ number_format($this->calculateTotalAmountForEmployee($employee, 'deduction'), 2) }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button type="button" wire:click="openIndividualModal({{ $employee->id }})"
                                    class="px-3 py-1.5 inline-flex items-center gap-1 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Manage Salary Structure
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No employees found matching the filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- MODAL 1: INDIVIDUAL SALARY STRUCTURE MODAL -->
    @if ($showIndividualModal && $selectedEmployee)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 overflow-y-auto py-10"
            wire:keydown.escape="closeModals">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-gray-100 space-y-5">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }}</h3>
                        <p class="text-xs text-gray-500 font-mono">
                            EMP ID: {{ $selectedEmployee->employee_id }} | Basic: LKR {{ number_format($selectedEmployee->salaryAndBank?->basic_salary ?? 0.00, 2) }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Add New Component Form Card -->
                <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl space-y-3">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">+ Add Allowance or Deduction</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Select Component</label>
                            <select wire:model.live="selectedComponentId" class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Choose Component --</option>
                                @foreach ($salaryComponents as $salaryComponent)
                                    <option value="{{ $salaryComponent->id }}">
                                        [{{ strtoupper($salaryComponent->type) }}] {{ $salaryComponent->name }} ({{ $salaryComponent->calculation_type === 'percentage' ? '% of Basic' : 'Fixed' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedComponentId') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            {{-- Clear Indicator of Amount vs Percentage with Limit Badge --}}
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase">
                                    {{ $individualCalculationType === 'percentage' ? 'Percentage (%)' : 'Amount (LKR)' }}
                                </label>
                                @if ($individualLimit)
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                        Max: {{ $individualCalculationType === 'percentage' ? $individualLimit . '%' : 'LKR ' . number_format($individualLimit, 2) }}
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input type="number" step="0.01" wire:model="individualAmount" 
                                    placeholder="{{ $individualCalculationType === 'percentage' ? 'e.g. 10 (%)' : 'e.g. 15000 (LKR)' }}"
                                    class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-semibold">
                                <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">
                                    {{ $individualCalculationType === 'percentage' ? '%' : 'LKR' }}
                                </span>
                            </div>
                            @error('individualAmount') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Frequency / Duration (Automatic based on Component Master Settings) --}}
                    @if ($selectedComponentId)
                        @if ($isIndividualComponentRecurring)
                            <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center gap-2.5 text-xs text-blue-900">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <div>
                                    <span class="font-bold">Recurring Monthly Component:</span>
                                    <span class="text-blue-700">Applies automatically to salary every month (Ongoing).</span>
                                </div>
                            </div>
                        @else
                            {{-- One-Time Component: Target Month is mandatory --}}
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-amber-900 font-bold">One-Time Target Month <span class="text-red-500">*</span></span>
                                    </div>
                                    <input type="month" wire:model="individualMonth" class="border border-amber-300 rounded-lg px-2.5 py-1 text-xs bg-white text-gray-800 font-semibold focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>
                                <p class="text-[11px] text-amber-700">This is a non-recurring component and will only be applied for the selected month.</p>
                                @error('individualMonth') <span class="text-xs text-red-500 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endif

                    <div class="flex justify-end">
                        <button type="button" wire:click="addComponentToEmployee"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase rounded-xl transition">
                            Assign Component
                        </button>
                    </div>
                </div>

                <!-- Assigned Components List -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Currently Assigned Components</h4>
                    <div class="border border-gray-100 rounded-xl overflow-hidden divide-y divide-gray-100 max-h-60 overflow-y-auto">
                        @forelse($selectedEmployee->salaryComponents as $assignedComponent)
                            <div class="p-3 flex items-center justify-between text-xs hover:bg-gray-50 transition">
                                <div>
                                    <span class="font-bold text-gray-900">{{ $assignedComponent->salaryComponent?->name }}</span>
                                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-semibold {{ $assignedComponent->salaryComponent?->type === 'earning' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ ucfirst($assignedComponent->salaryComponent?->type) }}
                                    </span>
                                    @if ($assignedComponent->applicable_month)
                                        <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Month: {{ $assignedComponent->applicable_month }}
                                        </span>
                                    @else
                                        <span class="ml-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                            Every Month
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Calculated Rupee Value display alongside percentage --}}
                                    <span class="font-bold text-gray-800">
                                        @if ($assignedComponent->salaryComponent?->calculation_type === 'percentage')
                                            {{ $assignedComponent->amount }}% 
                                            <span class="text-[11px] text-gray-400 font-normal">
                                                (LKR {{ number_format($this->getCalculatedRupeeAmount($assignedComponent, (float)($selectedEmployee->salaryAndBank?->basic_salary ?? 0)), 2) }})
                                            </span>
                                        @else
                                            LKR {{ number_format($assignedComponent->amount, 2) }}
                                        @endif
                                    </span>

                                    <button type="button" wire:click="toggleComponentStatus({{ $assignedComponent->id }})"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $assignedComponent->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                                        {{ $assignedComponent->is_active ? 'Active' : 'Disabled' }}
                                    </button>

                                    <button type="button" wire:click="removeComponentFromEmployee({{ $assignedComponent->id }})"
                                        class="text-red-500 hover:text-red-700" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-xs text-gray-400">No custom allowances or deductions assigned yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeModals" class="px-5 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endteleport
    @endif

    <!-- MODAL 2: BULK ASSIGNMENT MODAL -->
    @if ($showBulkModal)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 overflow-y-auto py-10"
            wire:keydown.escape="closeModals">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 space-y-4">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Bulk Assign Component</h3>
                    <button type="button" wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-500 uppercase mb-1">Select Salary Component <span class="text-red-500">*</span></label>
                        <select wire:model.live="bulkComponentId" class="w-full border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Select Component --</option>
                            @foreach ($salaryComponents as $salaryComponent)
                                <option value="{{ $salaryComponent->id }}">
                                    [{{ strtoupper($salaryComponent->type) }}] {{ $salaryComponent->name }} ({{ $salaryComponent->calculation_type === 'percentage' ? '% of Basic' : 'Fixed' }})
                                </option>
                            @endforeach
                        </select>
                        @error('bulkComponentId') <span class="text-red-500 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-gray-500 uppercase mb-1">Target Group <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer {{ $bulkTarget === 'category' ? 'border-blue-500 bg-blue-50 text-blue-900 font-bold' : 'border-gray-200 text-gray-700' }}">
                                <input type="radio" value="category" wire:model.live="bulkTarget" class="text-blue-600">
                                <span>By Category</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer {{ $bulkTarget === 'all' ? 'border-blue-500 bg-blue-50 text-blue-900 font-bold' : 'border-gray-200 text-gray-700' }}">
                                <input type="radio" value="all" wire:model.live="bulkTarget" class="text-blue-600">
                                <span>All Employees</span>
                            </label>
                        </div>
                    </div>

                    {{-- Category Selection Dropdown when By Category is chosen --}}
                    @if ($bulkTarget === 'category')
                        <div>
                            <label class="block font-bold text-gray-500 uppercase mb-1">Select Employee Category <span class="text-red-500">*</span></label>
                            <select wire:model="bulkCategoryId" class="w-full border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Choose Category --</option>
                                @foreach ($categories as $categoryItem)
                                    <option value="{{ $categoryItem->id }}">{{ $categoryItem->name }}</option>
                                @endforeach
                            </select>
                            @error('bulkCategoryId') <span class="text-red-500 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Bulk Frequency / Duration (Automatic based on Component Master Settings) --}}
                    @if ($bulkComponentId)
                        @if ($isBulkComponentRecurring)
                            <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center gap-2.5 text-xs text-blue-900">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <div>
                                    <span class="font-bold">Recurring Monthly Component:</span>
                                    <span class="text-blue-700">Applies to all selected employees every month (Ongoing).</span>
                                </div>
                            </div>
                        @else
                            {{-- One-Time Component: Target Month is mandatory --}}
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-amber-900 font-bold">One-Time Target Month <span class="text-red-500">*</span></span>
                                    </div>
                                    <input type="month" wire:model="bulkMonth" class="border border-amber-300 rounded-lg px-2.5 py-1 text-xs bg-white text-gray-800 font-semibold focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>
                                <p class="text-[11px] text-amber-700">This is a non-recurring component and will only be applied for the selected month.</p>
                                @error('bulkMonth') <span class="text-xs text-red-500 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endif

                    <div>
                        {{-- Clear Indicator of Amount vs Percentage with Limit Badge in Bulk Modal --}}
                        <div class="flex justify-between items-center mb-1">
                            <label class="block font-bold text-gray-500 uppercase">
                                {{ $bulkCalculationType === 'percentage' ? 'Percentage (%) of Basic Salary' : 'Fixed Amount (LKR)' }} <span class="text-red-500">*</span>
                            </label>
                            @if ($bulkLimit)
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                    Max: {{ $bulkCalculationType === 'percentage' ? $bulkLimit . '%' : 'LKR ' . number_format($bulkLimit, 2) }}
                                </span>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="number" step="0.01" wire:model="bulkAmount" 
                                placeholder="{{ $bulkCalculationType === 'percentage' ? 'e.g. 10 (for 10%)' : 'e.g. 5000.00 (LKR)' }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 font-bold">
                            <span class="absolute right-3 top-2 text-xs font-bold text-gray-400">
                                {{ $bulkCalculationType === 'percentage' ? '%' : 'LKR' }}
                            </span>
                        </div>
                        @error('bulkAmount') <span class="text-red-500 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Overwrite vs Skip Mode (The Conflict Resolver) --}}
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">If employee already has this component:</label>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="skip" wire:model="bulkOverwriteMode" class="text-blue-600">
                                <span class="text-gray-700 font-medium">Skip (Keep existing custom amounts safe)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="overwrite" wire:model="bulkOverwriteMode" class="text-blue-600">
                                <span class="text-gray-700 font-medium">Overwrite (Update everyone to this amount)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" wire:click="closeModals" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="button" wire:click="processBulkAssign" class="px-5 py-2 bg-blue-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-blue-700">
                        Apply to Selected Group
                    </button>
                </div>
            </div>
        </div>
    @endteleport
    @endif

</div>

</div>