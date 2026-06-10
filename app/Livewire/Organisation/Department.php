<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department as DepartmentModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Department extends Component
{
    use WithPagination;

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public string $name = '';
    public $code;
    public $description;

    // Delete Confirm State
    public $confirmDeleteId = null;

    /**
     * Define the pagination theme to use Tailwind classes.
     */
    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        activity()
            ->causedBy(auth()->user())
            ->tap(function ($activity) {
                $activity->ip_address = request()->ip();
                $activity->user_agent = request()->userAgent();
            })
            ->log('Visited Department Page');
    }
    protected function rules(): array
    {
        $orgId = Organisation::first(['id'])?->id;

        $codeUnique = $this->editingId
            ? "nullable|string|max:50|unique:departments,code,{$this->editingId},id,organisation_id,{$orgId}"
            : "nullable|string|max:50|unique:departments,code,NULL,id,organisation_id,{$orgId}";

        return [
            'name'        => 'required|string|max:255',
            'code'        => $codeUnique,
            'description' => 'nullable|string|max:250',
        ];
    }

    protected $validationAttributes = [
        'name' => 'Department Name',
        'code' => 'Department Code',
    ];

    /**
     * Reset pagination when searching or updating filters (if added later).
     */
    public function updating($property)
    {
        if ($property === 'search') {
            $this->resetPage();
        }
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record              = DepartmentModel::findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $record->name;
        $this->code          = $record->code;
        $this->description   = $record->description;
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::first(['id'])?->id;

        $data = [
            'organisation_id' => $orgId,
            'name'            => $this->name,
            'code'            => $this->code,
            'description'     => $this->description,
        ];

        if ($this->editingId) {
            DepartmentModel::findOrFail($this->editingId)->update($data);
        } else {
            DepartmentModel::create($data);
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            DepartmentModel::findOrFail($this->confirmDeleteId)->delete();
            $this->confirmDeleteId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->code        = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        $org = Organisation::first(['id']);

        // Fetch paginated data
        // If $org is null, we create an empty query that results in an empty Paginator
        $departments = DepartmentModel::query()
            ->when($org, fn($query) => $query->where('organisation_id', $org->id), fn($query) => $query->whereRaw('1 = 0'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.organisation.department', [
            'departmentList' => $departments
        ]);
    }
}
