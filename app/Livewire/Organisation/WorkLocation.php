<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkLocation as WorkLocationModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class WorkLocation extends Component
{
    use WithPagination;

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public string $name           = '';
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $locationState  = '';
    public string $city           = '';
    public string $pincode        = '';

    // Delete Confirm State
    public $confirmDeleteId = null;

    /**
     * Set pagination theme to Tailwind
     */
    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'locationState'  => 'required|string|max:255',
            'city'           => 'required|string|max:255',
            'pincode'        => 'required|string|max:20',
        ];
    }

    protected $validationAttributes = [
        'name'           => 'Work Location Name',
        'address_line_1' => 'Address Line 1',
        'locationState'  => 'State',
        'city'           => 'City',
        'pincode'        => 'Pincode',
    ];

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record               = WorkLocationModel::findOrFail($id);
        $this->editingId      = $id;
        $this->name           = $record->name;
        $this->address_line_1 = $record->address_line_1;
        $this->address_line_2 = $record->address_line_2 ?? '';
        $this->locationState  = $record->state;
        $this->city           = $record->city;
        $this->pincode        = $record->pincode;
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::query()->first()?->id;

        $data = [
            'organisation_id' => $orgId,
            'name'            => $this->name,
            'address_line_1'  => $this->address_line_1,
            'address_line_2'  => $this->address_line_2 ?: null,
            'state'           => $this->locationState,
            'city'            => $this->city,
            'pincode'         => $this->pincode,
        ];

        try {
            DB::transaction(function () use ($data) {


                if ($this->editingId) {
                    WorkLocationModel::findOrFail($this->editingId)->update($data);
                    $message = 'Work Location updated successfully.';
                } else {
                    WorkLocationModel::create($data);
                    $message = 'Work Location created successfully.';
                }
                 $this->dispatch('toast', type: 'success', message: $message);
            });

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Work Location could not be saved.');
            \Log::error('Work Location save error: ' . $e->getMessage());
            return;
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            try {
                DB::transaction(function () {
                    WorkLocationModel::findOrFail($this->confirmDeleteId)->delete();
                    $this->dispatch('toast', type: 'success', message: 'Work Location deleted successfully.');
                });

                $this->confirmDeleteId = null;
            } catch (\Exception $e) {
                 $this->dispatch('toast', type: 'error', message: 'Something went wrong! Work Location could not be deleted.');
                \Log::error('Work Location delete error: ' . $e->getMessage());
                return;
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
        $this->editingId      = null;
        $this->name           = '';
        $this->address_line_1 = '';
        $this->address_line_2 = '';
        $this->locationState  = '';
        $this->city           = '';
        $this->pincode        = '';
        $this->resetValidation();
    }

    public function render()
    {
        $org = Organisation::first(['id']);

        // Fetch paginated locations for the current organisation
        $locations = WorkLocationModel::query()
            ->when($org, fn($query) => $query->where('organisation_id', $org->id), fn($query) => $query->whereRaw('1 = 0'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.organisation.work-location', [
            'locationList' => $locations
        ]);
    }
}
