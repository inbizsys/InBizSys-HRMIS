<?php

namespace App\Livewire\LeaveManagement;

use App\Models\HolidayCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HolidayCategoryManagement extends Component
{
    use WithPagination;

    public $search = '';
    
    // Modal controls
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;
    public $editingCategoryId = null;

    // Form fields
    public $name = '';
    public $color_code = '#e11d48';
    public $description = '';

    // Delete properties
    public $categoryIdToDelete;
    public $categoryNameToDelete;

    protected $rules = [
        'name' => 'required|string|max:255|unique:holiday_categories,name',
        'color_code' => 'required|string|max:50',
        'description' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->editingCategoryId = $id;

        $category = HolidayCategory::findOrFail($id);
        $this->name = $category->name;
        $this->color_code = $category->color_code ?? '#e11d48';
        $this->description = $category->description;

        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->color_code = '#e11d48';
        $this->description = '';
        $this->isEditMode = false;
        $this->editingCategoryId = null;
        $this->showModal = false;
        $this->resetValidation();
    }

    public function saveCategory()
    {
        if ($this->isEditMode) {
            $this->rules['name'] = 'required|string|max:255|unique:holiday_categories,name,' . $this->editingCategoryId;
        }

        $this->validate();

        if ($this->isEditMode) {
            $category = HolidayCategory::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->name,
                'color_code' => $this->color_code,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Holiday category updated successfully!');
        } else {
            HolidayCategory::create([
                'name' => $this->name,
                'color_code' => $this->color_code,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Holiday category created successfully!');
        }

        $this->resetForm();
    }

    public function confirmDelete($id, $name)
    {
        $this->categoryIdToDelete = $id;
        $this->categoryNameToDelete = $name;
        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        if ($this->categoryIdToDelete) {
            $category = HolidayCategory::findOrFail($this->categoryIdToDelete);
            $category->delete();
            $this->showDeleteModal = false;
            session()->flash('message', 'Holiday category deleted successfully!');
        }
    }

    public function render()
    {
        $categories = HolidayCategory::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.LeaveManagement.holiday-category-management', [
            'categories' => $categories
        ]);
    }
}