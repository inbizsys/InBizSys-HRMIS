<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department as DepartmentModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public $supervisor_id;

    // Delete Confirm State
    public $confirmDeleteId = null;

    /**
     * Define the pagination theme to use Tailwind classes.
     */
    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        activity()
            ->causedBy(Auth::user())
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
            'supervisor_id' => 'nullable|exists:employees,id',
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
        $this->supervisor_id = $record->supervisor_id;
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
            'supervisor_id' => $this->supervisor_id ?: null,
        ];

        try {
            DB::transaction(function () use ($data) {

                if ($this->editingId) {
                    $department = DepartmentModel::findOrFail($this->editingId);
                    $department ->update($data);
                    $message = 'Department updated successfully.';
                } else {
                    $department = DepartmentModel::create($data);
                    $message = 'Department added successfully.';
                }

                if ($this->supervisor_id) {
                    Employee::where('id', $this->supervisor_id)->update([
                        'department_id' => $department->id
                    ]);
                }

                $this->dispatch('notify', type: 'success', message: $message);
            });
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Something went wrong! Department could not be saved.');
            Log::error('Department save error: ' . $e->getMessage());
            return;
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
            $departmentId = $this->confirmDeleteId; // save the ID before resetting it for UI purposes

            try {
                DB::transaction(function () use ($departmentId) {
                    DepartmentModel::findOrFail($departmentId)->delete();
                    $this->dispatch('notify', type: 'success', message: 'Department deleted successfully.');
                });

                $this->confirmDeleteId = null;
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: 'Something went wrong! Department could not be deleted.');
                Log::error('Department delete error: ' . $e->getMessage());
            }
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
        $this->supervisor_id = '';
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

        $employees = Employee::whereHas('user', function ($queryUser) {
            $queryUser->whereHas('roles', function ($queryRole) {
                $queryRole->where('name', 'Supervisor');
            });
        })
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->select('employees.*')
            ->get();

        return view('livewire.organisation.department', [
            'departmentList' => $departments,
            'employeeList'   => $employees
        ]);
    }
}
