<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Designation as DesignationModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        $orgId = Organisation::query()->first()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('designations', 'name')
                    ->ignore($this->editingId)
                    ->where(fn($query) => $query->where('organisation_id', $orgId))
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
        $record          = DesignationModel::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::query()->first()?->id;

        $data = [
            'organisation_id' => $orgId,
            'name'            => $this->name,
        ];

        try {
            DB::transaction(function () use ($data) {
                if ($this->editingId) {
                    DesignationModel::findOrFail($this->editingId)->update($data);
                    $message = 'Designation updated successfully.';
                } else {
                    DesignationModel::create($data);
                    $message = 'Designation added successfully.';
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Designation could not be saved.');
            \Log::error('Designation save error: ' . $e->getMessage());
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
            DesignationModel::findOrFail($this->confirmDeleteId)->delete();
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
        $org = Organisation::first(['id']);

        // Database eken pagination ekka data load kirima
        $designations = DesignationModel::query()
            ->when($org, fn($query) => $query->where('organisation_id', $org->id), fn($query) => $query->whereRaw('1 = 0'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.organisation.designation', [
            'designationList' => $designations
        ]);
    }
}
