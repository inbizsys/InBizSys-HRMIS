<?php

namespace App\Livewire\Payroll;

use App\Models\EmployeeCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EmployeeCategoryManager extends Component
{
    use WithPagination;

    public ?int $editingCategoryId = null;
    public string $name = '';
    public string $code = '';
    public $min_basic_salary = 0.00;
    public $max_basic_salary = null;
    public bool $is_ot_eligible = false;
    public int $ot_divisor_hours = 200;
    public $ot_calculation_rate = 1.50;
     public $ot_weekend_rate = 1.50; 
    public $ot_holiday_rate = 2.00; 
    public int $standard_monthly_days = 30;
    public $max_deduction_percentage = 60.00;
    public bool $is_active = true;

    // Modals
    public bool $showCategoryModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;

    // View & Delete context
    public ?EmployeeCategory $selectedCategory = null;
    public ?int $deletingCategoryId = null;
    public string $deletingCategoryName = '';
    public int $assignedEmployeeCount = 0; // Linked employee count track madalu

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:employee_categories,code,' . $this->editingCategoryId,
            'min_basic_salary' => 'required|numeric|min:0',
            'max_basic_salary' => 'nullable|numeric|gte:min_basic_salary',
            'is_ot_eligible' => 'boolean',
            'ot_divisor_hours' => 'required|integer|min:1|max:500',
            'ot_calculation_rate' => 'required|numeric|min:1',
            'ot_weekend_rate'     => 'required|numeric|min:1', 
            'ot_holiday_rate'     => 'required|numeric|min:1', 
            'standard_monthly_days' => 'required|integer|min:1|max:31',
            'max_deduction_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCategoryModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $category = EmployeeCategory::findOrFail($id);

        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->code = $category->code;
        $this->min_basic_salary = $category->min_basic_salary;
        $this->max_basic_salary = $category->max_basic_salary;
        $this->is_ot_eligible = (bool) $category->is_ot_eligible;
        $this->ot_divisor_hours = $category->ot_divisor_hours;
        $this->ot_calculation_rate = $category->ot_calculation_rate;
        $this->ot_weekend_rate     = $category->ot_weekend_rate ?? 1.50; 
        $this->ot_holiday_rate     = $category->ot_holiday_rate ?? 2.00; 
        $this->standard_monthly_days = $category->standard_monthly_days;
        $this->max_deduction_percentage = $category->max_deduction_percentage ?? 60.00;
        $this->is_active = (bool) $category->is_active;

        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $validated = $this->validate();

        // Convert empty string to null to prevent Decimal casting error in Eloquent
        $validated['max_basic_salary'] = ($this->max_basic_salary !== null && trim((string) $this->max_basic_salary) !== '')
            ? $this->max_basic_salary
            : null;

        EmployeeCategory::updateOrCreate(
            ['id' => $this->editingCategoryId],
            $validated
        );

        session()->flash('success', $this->editingCategoryId ? 'Employee Category updated successfully.' : 'New Employee Category created successfully.');

        $this->closeModals();
        $this->resetForm();
    }

    public function viewCategory(int $id): void
    {
        $this->selectedCategory = EmployeeCategory::findOrFail($id);
        $this->showViewModal = true;
    }

    // Delete click madidaga employees table nalli check maduvudu
    public function confirmDelete(int $id, string $name): void
    {
        $this->deletingCategoryId = $id;
        $this->deletingCategoryName = $name;

        // Employees table nalli e category id idiya antha count maduthe
        $category = EmployeeCategory::withCount('employees')->findOrFail($id);
        $this->assignedEmployeeCount = $category->employees_count;

        $this->showDeleteModal = true;
    }

    // Direct delete aagada haage check maduva final safety logic
    public function deleteCategory(): void
    {
        if (!$this->deletingCategoryId) {
            $this->closeModals();
            return;
        }

        $category = EmployeeCategory::withCount('employees')->findOrFail($this->deletingCategoryId);

        // Employee count 1 athava hecchidre delete aaguvudilla
        if ($category->employees_count > 0) {
            session()->flash('error', "Deletion blocked! {$category->employees_count} employee(s) are assigned to this category.");
            $this->closeModals();
            return;
        }

        $category->delete();
        session()->flash('success', 'Employee Category deleted successfully.');
        $this->closeModals();
    }

    public function toggleCategoryStatus(int $id): void
    {
        $category = EmployeeCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        session()->flash('success', 'Category status updated successfully.');
    }

    public function closeModals(): void
    {
        $this->showCategoryModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->assignedEmployeeCount = 0;
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->editingCategoryId = null;
        $this->name = '';
        $this->code = '';
        $this->min_basic_salary = 0.00;
        $this->max_basic_salary = null;
        $this->is_ot_eligible = false;
        $this->ot_divisor_hours = 200;
        $this->ot_calculation_rate = 1.50;
         $this->ot_weekend_rate     = 1.50;
        $this->ot_holiday_rate     = 2.00; 
        $this->standard_monthly_days = 30;
        $this->max_deduction_percentage = 60.00;
        $this->is_active = true;
        $this->assignedEmployeeCount = 0;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.payroll.employee-category-manager', [
            'categories' => EmployeeCategory::latest()->paginate(10),
        ]);
    }
}
