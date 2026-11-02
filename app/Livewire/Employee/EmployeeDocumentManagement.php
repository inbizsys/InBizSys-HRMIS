<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use App\Models\DocumentCategory;
use App\Models\EmployeeDocument;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
class EmployeeDocumentManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $activeTab = 'all';

    // Modals
    public $showUploadModal = false;
    public $showDeleteModal = false;

    // View Modal Controls
    public $showViewModal = false;
    public $viewingFileUrl = '';
    public $viewingFileType = '';

    // Form Data
    public $selectedEmployeeId = '';
    public $selectedEmployeeName = '';
    public $rows = [];

    // Employee Dropdown Controls
    public $empSearch = '';
    public $showEmpDropdown = false;

    // Delete
    public $documentIdToDelete;
    public $documentNameToDelete;

    // Edit Controls
    public $isEditMode = false;
    public $editingDocumentId = null;


    public function mount()
    {
        $this->resetForm();
    }


    // Add a new document row to the dynamic form array with default or provided values
    public function addRow($categoryId = '', $categoryName = '', $isRequired = false)
    {
        $this->rows[] = [
            'id' => uniqid(),
            'document_id' => null,
            'document_category_id' => $categoryId,
            'category_name' => $categoryName,
            'cat_search' => $categoryName,
            'show_dropdown' => false,
            'file' => null,
            'existing_file_path' => null,
            'is_required' => $isRequired
        ];
    }


    /**
     * Removes a specific row from the document list.
     * Handles file deletion and database removal if in Edit Mode.
     */
    public function removeRow($index)
    {
        if (isset($this->rows[$index]['is_required']) && $this->rows[$index]['is_required'] && !$this->isEditMode) {
            return;
        }

        if ($this->isEditMode && isset($this->rows[$index]['document_id'])) {
            $docId = $this->rows[$index]['document_id'];
            $doc = EmployeeDocument::find($docId);

            if ($doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->resetValidation();

        foreach ($this->rows as $key => $row) {
            $this->rows[$key]['show_dropdown'] = false;
        }

        if ($this->isEditMode) {
            $this->showUploadModal = false;
            session()->flash('message', 'Document removed successfully!');
        }
    }


    /**
     * Opens the upload modal, resets the form, 
     * and automatically prepopulates required document categories.
     */
    public function openUploadModal()
    {
        $this->resetForm();
        $this->isEditMode = false;

        $requiredCategories = DocumentCategory::where('is_required', true)->get();

        if ($requiredCategories->count() > 0) {
            $this->rows = [];
            foreach ($requiredCategories as $cat) {
                $this->addRow($cat->id, $cat->name, true);
            }
        }

        $this->showUploadModal = true;
    }

    /**
     * Opens the modal in Edit Mode and populates the form 
     * with existing document and employee details from the database.
     */

    public function openEditModal($documentId)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->editingDocumentId = $documentId;

        $doc = EmployeeDocument::with(['category', 'employee'])->findOrFail($documentId);

        $this->selectedEmployeeId = $doc->employee_id;
        $this->selectedEmployeeName = $doc->employee->first_name . ' ' . $doc->employee->last_name;
        $this->empSearch = $this->selectedEmployeeName;

        $this->rows = [[
            'id' => uniqid(),
            'document_id' => $doc->id,
            'document_category_id' => $doc->document_category_id,
            'category_name' => $doc->category->name ?? '',
            'cat_search' => $doc->category->name ?? '',
            'show_dropdown' => false,
            'file' => null,
            'existing_file_path' => $doc->file_path,
            'is_required' => (bool)($doc->category->is_required ?? false)
        ]];

        $this->showUploadModal = true;
    }

    /**
     * Determines the file type and either displays it in a preview modal 
     * (for images and PDFs) or forces a direct download (for Word/Excel/other files).
     */
    public function viewDocument($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $this->viewingFileUrl = asset('storage/' . $filePath);

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $this->viewingFileType = 'image';
            $this->showViewModal = true;
        } elseif ($extension === 'pdf') {
            $this->viewingFileType = 'pdf';
            $this->showViewModal = true;
        } else {
            return response()->download(storage_path('app/public/' . $filePath));
        }
    }

    /**
     * Sets the selected employee details from the search dropdown 
     * and closes the dropdown list.
     */
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->selectedEmployeeName = $name;
        $this->empSearch = $name;
        $this->showEmpDropdown = false;
    }

    /**
     * Sets the selected document category for a specific row in the dynamic form
     * and closes that row's search dropdown list.
     */
    public function selectCategory($index, $id, $name)
    {
        $this->rows[$index]['document_category_id'] = $id;
        $this->rows[$index]['category_name'] = $name;
        $this->rows[$index]['cat_search'] = $name;
        $this->rows[$index]['show_dropdown'] = false;
    }


    /**
     * Livewire lifecycle hook triggered automatically when any property is updated.
     * If a row's category search input is cleared, it resets that row's category data.
     */
    public function updated($key, $value)
    {
        if (str_contains($key, 'rows.') && str_contains($key, '.cat_search')) {
            $parts = explode('.', $key);
            $index = $parts[1];

            if (empty(trim($value))) {
                $this->rows[$index]['document_category_id'] = '';
                $this->rows[$index]['category_name'] = '';
                $this->rows[$index]['file'] = null;
            }
        }
    }


    /**
     * Fetches optional document categories from the database for a specific row's dropdown.
     * Returns the top 10 general categories if the search is empty, or filters by the typed keyword.
     */
    public function getCategoriesForRow($index)
    {
        $searchWord = isset($this->rows[$index]['cat_search']) ? trim($this->rows[$index]['cat_search']) : '';

        $query = DocumentCategory::where('is_required', false);

        if (empty($searchWord) || $searchWord === ($this->rows[$index]['category_name'] ?? '')) {
            return $query->take(10)->get();
        }

        return $query->where('name', 'like', '%' . $searchWord . '%')->take(10)->get();
    }


    // Reset Form
    public function resetForm()
    {
        $this->selectedEmployeeId = '';
        $this->selectedEmployeeName = '';
        $this->empSearch = '';
        $this->showEmpDropdown = false;
        $this->isEditMode = false;
        $this->editingDocumentId = null;
        $this->showUploadModal = false; 

        $this->rows = [[
            'id' => uniqid(),
            'document_id' => null,
            'document_category_id' => '',
            'category_name' => '',
            'cat_search' => '',
            'show_dropdown' => false,
            'file' => null,
            'existing_file_path' => null,
            'is_required' => false
        ]];
        $this->resetValidation();
    }

    /**
     * Validates form data, handles physical file uploads (storing new files 
     * and deleting old ones), and saves/updates document records in the database.
     */
    public function saveDocuments()
    {
        $rules = [
            'selectedEmployeeId' => 'required|exists:employees,id',
            'rows.*.document_category_id' => 'required|exists:document_categories,id',
        ];

        foreach ($this->rows as $index => $row) {
          
            if ($this->isEditMode && !empty($row['document_id'])) {
                $rules["rows.{$index}.file"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
            } else {
                $rules["rows.{$index}.file"] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            }
        }

        $this->validate($rules, [
            'selectedEmployeeId.required' => 'Please select an employee.',
            'rows.*.document_category_id.required' => 'Please select a category.',
            'rows.*.file.required' => 'Upload a file.',
            'rows.*.file.mimes' => 'Only PDF, JPG, JPEG, and PNG files are allowed.',
        ]);

        foreach ($this->rows as $row) {
            $filePath = $row['existing_file_path'] ?? null;

            if (isset($row['file']) && $row['file']) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $row['file']->store('employee_documents', 'public');
            }

            if ($this->isEditMode && isset($row['document_id']) && $row['document_id']) {
                EmployeeDocument::where('id', $row['document_id'])->update([
                    'document_category_id' => $row['document_category_id'],
                    'file_path' => $filePath,
                ]);
            } else {
                EmployeeDocument::create([
                    'employee_id' => $this->selectedEmployeeId,
                    'document_category_id' => $row['document_category_id'],
                    'file_path' => $filePath,
                ]);
            }
        }

        $this->showUploadModal = false;
        $msg = $this->isEditMode ? 'Document updated successfully!' : 'Documents uploaded successfully!';
        $this->resetForm();
        session()->flash('message', $msg);
    }

    // Confirm delete
    public function confirmDelete($id, $employeeName, $categoryName)
    {
        $this->documentIdToDelete = $id;
        $this->documentNameToDelete = $employeeName . "'s " . $categoryName;
        $this->showDeleteModal = true;
    }


    // delete Document
    public function deleteDocument()
    {
        if ($this->documentIdToDelete) {
            $doc = EmployeeDocument::findOrFail($this->documentIdToDelete);
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
            $this->showDeleteModal = false;
            session()->flash('message', 'Document deleted successfully!');
        }
    }
    

    // Tab Change
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }


    // Render
    public function render()
    {
        $filteredEmployees = [];
        if ($this->showEmpDropdown) {
            $filteredEmployees = Employee::query()
                ->when($this->empSearch, function ($query) {
                    $query->where('first_name', 'like', '%' . $this->empSearch . '%')
                        ->orWhere('last_name', 'like', '%' . $this->empSearch . '%');
                })
                ->take(10)
                ->get();
        }

        $documentsQuery = EmployeeDocument::with(['employee', 'category'])
            ->whereHas('employee', function ($documentquery) {
                $documentquery->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });

        if ($this->activeTab !== 'all') {
            $documentsQuery->where('document_category_id', $this->activeTab);
        }

        return view('livewire.employee.employee-document-management', [
            'documents' => $documentsQuery->latest()->paginate(10),
            'allCategories' => DocumentCategory::all(),
            'employees' => $filteredEmployees
        ]);
    }
}
