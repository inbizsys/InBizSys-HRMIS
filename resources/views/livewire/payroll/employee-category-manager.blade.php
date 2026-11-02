<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header Section -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Employee Categories & Salary Grades</h1>
            <p class="text-sm text-gray-500 mt-1">Configure job tiers, salary boundaries, overtime eligibility, and statutory deduction divisors.</p>
        </div>

        <button type="button" wire:click="openCreateModal"
            class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition">
            + Add New Category
        </button>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Categories Data Table -->
    <div class="w-full bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Category & Code</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Salary Band (LKR)</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">OT Policy</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Day Rate Basis</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-800">{{ $category->name }}</div>
                                <div class="text-xs font-mono font-semibold text-slate-500">{{ $category->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                <div>Min: <span class="font-bold text-gray-800">{{ number_format($category->min_basic_salary, 2) }}</span></div>
                                <div>Max: 
                                    @if ($category->max_basic_salary && $category->max_basic_salary > 0)
                                        <span class="font-bold text-gray-800">{{ number_format($category->max_basic_salary, 2) }}</span>
                                    @else
                                        <span class="text-gray-400 italic font-normal">No limit</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                @if ($category->is_ot_eligible)
                                    <div class="text-emerald-700 font-bold">Eligible ({{ $category->ot_calculation_rate }}x)</div>
                                    <div class="text-gray-400">Divisor: {{ $category->ot_divisor_hours }} hrs</div>
                                @else
                                    <div class="text-gray-400 font-semibold">Not Eligible</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                                {{ $category->standard_monthly_days }} Days / Month
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" wire:click="toggleCategoryStatus({{ $category->id }})"
                                    class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ $category->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">

                                    {{-- View Button --}}
                                    <button type="button" wire:click="viewCategory({{ $category->id }})"
                                        title="View Details"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>

                                    <span class="text-gray-200 select-none">|</span>

                                    {{-- Edit Button --}}
                                    <button type="button" wire:click="openEditModal({{ $category->id }})"
                                        title="Edit Category"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <span class="text-gray-200 select-none">|</span>

                                    {{-- Delete Button --}}
                                    <button type="button" wire:click="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                        title="Delete Category"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No employee categories defined yet. Click "+ Add New Category" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Modal 1: Create / Edit Category -->
    @if ($showCategoryModal)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto py-10">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4">
                    {{ $editingCategoryId ? 'Edit Employee Category' : 'Create New Employee Category' }}
                </h3>

                <form wire:submit="saveCategory" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category Name</label>
                            <input type="text" wire:model="name" placeholder="e.g. Executive Staff"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('name')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category Code</label>
                            <input type="text" wire:model="code" placeholder="e.g. EXEC"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-mono">
                            @error('code')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Basic Salary (LKR) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="min_basic_salary"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                            @error('min_basic_salary')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                Max Basic Salary (LKR) <span class="text-xs font-normal text-gray-400 lowercase">(optional)</span>
                            </label>
                            <input type="number" step="0.01" wire:model="max_basic_salary" placeholder="Optional — leave blank for no limit"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                            @error('max_basic_salary')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl space-y-3">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_ot_eligible_toggle" wire:model.live="is_ot_eligible"
                                class="rounded text-blue-600 focus:ring-blue-500">
                            <label for="is_ot_eligible_toggle" class="text-sm font-semibold text-gray-700">Eligible for Overtime (OT)</label>
                        </div>

                         @if ($is_ot_eligible)
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">OT Divisor Hours</label>
                                    <input type="number" wire:model="ot_divisor_hours" placeholder="e.g. 200"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-gray-400 mt-1">Default: 200 hrs</p>
                                    @error('ot_divisor_hours') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Normal OT Rate</label>
                                    <input type="number" step="0.10" wire:model="ot_calculation_rate" placeholder="1.50"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-gray-400 mt-1">Weekday: 1.50x</p>
                                    @error('ot_calculation_rate') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Weekend OT Rate</label>
                                    <input type="number" step="0.10" wire:model="ot_weekend_rate" placeholder="1.50"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-gray-400 mt-1">Rest Day: 1.50x or 2.0x</p>
                                    @error('ot_weekend_rate') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Public / Holiday OT Rate</label>
                                    <input type="number" step="0.10" wire:model="ot_holiday_rate" placeholder="2.00"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-gray-400 mt-1">Mercantile / Poya: 2.00x (Double OT)</p>
                                    @error('ot_holiday_rate') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Monthly Day Divisor (No-Pay)</label>
                            <input type="number" wire:model="standard_monthly_days" placeholder="e.g. 30, 26, or 22"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                            <p class="text-[11px] text-gray-400 mt-1">Default: 30 days (Office) or 26 days (Factory)</p>
                            @error('standard_monthly_days')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Max Deduction Limit (%)</label>
                            <input type="number" step="0.01" wire:model="max_deduction_percentage" placeholder="60.00"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                            <p class="text-[11px] text-gray-400 mt-1">e.g. 60% (Shop & Office), 50% (Wages Board)</p>
                            @error('max_deduction_percentage')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="category_is_active_toggle" wire:model="is_active"
                            class="rounded text-blue-600 focus:ring-blue-500">
                        <label for="category_is_active_toggle" class="text-sm font-semibold text-gray-700">Active Category</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" wire:click="closeModals"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-blue-700 transition">
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Modal 2: View Details -->
    @if ($showViewModal && $selectedCategory)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $selectedCategory->name }}</h3>
                        <span class="text-xs font-mono font-semibold text-slate-500">{{ $selectedCategory->code }}</span>
                    </div>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $selectedCategory->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $selectedCategory->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Salary Range:</span>
                        <span class="font-bold text-gray-800">
                            @if ($selectedCategory->max_basic_salary && $selectedCategory->max_basic_salary > 0)
                                LKR {{ number_format($selectedCategory->min_basic_salary, 2) }} - {{ number_format($selectedCategory->max_basic_salary, 2) }}
                            @else
                                LKR {{ number_format($selectedCategory->min_basic_salary, 2) }}+ (No Upper Limit)
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">OT Eligible:</span>
                        <span class="font-bold {{ $selectedCategory->is_ot_eligible ? 'text-emerald-600' : 'text-gray-500' }}">
                            {{ $selectedCategory->is_ot_eligible ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    @if ($selectedCategory->is_ot_eligible)
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">OT Divisor Hours:</span>
                            <span class="font-bold text-slate-700">{{ $selectedCategory->ot_divisor_hours }} Hours</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">OT Multiplier:</span>
                            <span class="font-bold text-blue-600">{{ $selectedCategory->ot_calculation_rate }}x</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-1">
                        <span class="text-gray-500">No-Pay Base Divisor:</span>
                        <span class="font-bold text-gray-800">{{ $selectedCategory->standard_monthly_days }} Days</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeModals"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endteleport
    @endif

    <!-- Modal 3: Safe Delete Modal -->
@if ($showDeleteModal)
@teleport('body')
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-100 text-center space-y-4">

            @if ($assignedEmployeeCount > 0)
                <!-- Employee assigned restriction message in English -->
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div>
                    <h4 class="text-base font-bold text-gray-900">Deletion Blocked!</h4>
                    <p class="text-xs text-gray-600 mt-2">
                        Category <span class="font-bold text-gray-900">"{{ $deletingCategoryName }}"</span> cannot be deleted because there are <span class="font-bold text-rose-600">{{ $assignedEmployeeCount }} employee(s)</span> assigned to another category.
                    </p>
                    <p class="text-[11px] text-gray-400 mt-1.5">
                        Please reassign those employees to another category first, then try deleting again.
                    </p>
                </div>

                <div class="flex justify-center pt-2">
                    <button type="button" wire:click="closeModals"
                        class="px-5 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Close
                    </button>
                </div>
            @else
                <!-- Normal delete confirmation modal in English -->
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div>
                    <h4 class="text-base font-bold text-gray-900">Delete Category?</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        Are you sure you want to delete category <span class="font-bold text-gray-800">"{{ $deletingCategoryName }}"</span>? This action cannot be undone.
                    </p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button type="button" wire:click="closeModals"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteCategory"
                        class="px-4 py-2 bg-red-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            @endif

        </div>
    </div>
@endteleport
@endif

</div>
