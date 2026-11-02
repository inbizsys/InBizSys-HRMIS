<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


#[Layout('layouts.app')]
class Designation extends Component
{
    use WithPagination;

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public string $name = '';

    // Delete Confirm State
    public $confirmDeleteId = null;

    /**
     * Tailwind pagination theme use kirima
     */
    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($this->editingId)
            ],
        ];
    }

    protected $validationAttributes = [
        'name' => 'Designation Name',
    ];

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record          = Role::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'            => $this->name,
            'guard_name' => 'web',
        ];

        try {
            DB::transaction(function () use ($data) {
                if ($this->editingId) {
                    Role::findOrFail($this->editingId)->update($data);
                    $message = 'Designation updated successfully.';
                } else {
                    Role::create($data);
                    $message = 'Designation added successfully.';
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Designation could not be saved.');
            Log::error('Designation save error: ' . $e->getMessage());
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
            Role::findOrFail($this->confirmDeleteId)->delete();
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
        $this->editingId = null;
        $this->name      = '';
        $this->resetValidation();
    }

    public function render()
    {
        

        // Database eken pagination ekka data load kirima
        $roles = Role::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.organisation.designation', [
            'designationList' => $roles
        ]);
    }
}
