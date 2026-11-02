<?php

namespace App\Livewire\Employee;

use App\Models\DocumentCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class DocumentCategoryManagement extends Component
{
    use WithPagination;

    // Search and Form Properties
    public $search = '';
    public $name, $description, $is_required = false;

    // Modal Control Properties (Pure Livewire)
    public $showModal = false;
    public $isEditMode = false;
    public $selectedCategoryId;

    // Delete Modal Properties
    public $showDeleteModal = false;
    public $categoryIdToDelete;
    public $categoryNameToDelete;
    public $isCategoryUsed = false;

    
    // Validation Rules (Ignores current ID during unique check when editing)
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:document_categories,name,' . $this->selectedCategoryId,
            'description' => 'nullable|string|max:500',
            'is_required' => 'boolean',
        ];
    }


    // Reset pagination when user types in search bar
    public function updatingSearch()
    {
        $this->resetPage();
    }


    // Open Create Modal
    public function openAddModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }


    // Open Edit Modal and Load Data
    public function editCategory($id)
    {
        $this->resetValidation();
        $this->isEditMode = true;

        $category = DocumentCategory::findOrFail($id);
        $this->selectedCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_required = (bool) $category->is_required;

        $this->showModal = true;
    }


    // Trigger Delete Confirmation Modal and Check Usage
    public function confirmDelete($id, $name)
    {
        $this->categoryIdToDelete = $id;
        $this->categoryNameToDelete = $name;

        // Check if this category is currently being used in employee_documents table
        $this->isCategoryUsed = DB::table('employee_documents')
            ->where('document_category_id', $id)
            ->exists();

        $this->showDeleteModal = true;
    }


    // General method to close form modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }


    // Clear Form Fields and Validations
    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->is_required = false;
        $this->selectedCategoryId = null;
        $this->resetValidation();
    }


    // Store a New Document Category
    public function saveCategory()
    {
        $this->validate();

        DocumentCategory::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_required' => $this->is_required,
        ]);

        $this->showModal = false;
        $this->resetForm();

        session()->flash('message', 'Document Category added successfully!');
    }


    // Update an Existing Document Category
    public function updateCategory()
    {
        $this->validate();

        if ($this->selectedCategoryId) {
            $category = DocumentCategory::findOrFail($this->selectedCategoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_required' => $this->is_required,
            ]);

            $this->showModal = false;
            $this->resetForm();

            session()->flash('message', 'Document Category updated successfully!');
        }
    }


    // Delete the Category from Database
    public function deleteCategory()
    {
        if ($this->categoryIdToDelete) {
            $category = DocumentCategory::findOrFail($this->categoryIdToDelete);
            $category->delete();

            $this->showDeleteModal = false;
            $this->categoryIdToDelete = null;
            $this->categoryNameToDelete = '';

            session()->flash('message', 'Document Category deleted successfully!');
        }
    }


    public function render()
    {
        // Render view with filtered and paginated document categories
        $categories = DocumentCategory::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.employee.document-category-management', [
            'categories' => $categories
        ]);
    }
    
}