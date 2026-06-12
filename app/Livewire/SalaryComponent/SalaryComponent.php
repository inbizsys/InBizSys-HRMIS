<?php

namespace App\Livewire\SalaryComponent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SalaryComponent as SalaryComponentModel;
use App\Models\Currency;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class SalaryComponent extends Component
{
    use WithPagination;

    // Active tab: 'earning' | 'deduction'
    public string $activeTab = 'earning';

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public string $type          = 'earning';
    public string $name          = '';
    public string $nameInPayslip = '';
    public $limit                = null;
    public bool $isActive        = true;

    // Delete Confirm State
    public $confirmDeleteId = null;

    // Is the record being edited a system record?
    public bool $isSystemRecord = false;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        $orgId = Organisation::first(['id'])?->id;

        $nameUnique = $this->editingId
            ? "required|string|max:255|unique:salary_components,name,{$this->editingId},id,organisation_id,{$orgId},type,{$this->type}"
            : "required|string|max:255|unique:salary_components,name,NULL,id,organisation_id,{$orgId},type,{$this->type}";

        return [
            'type'          => 'required|in:earning,deduction',
            'name'          => $nameUnique,
            'nameInPayslip' => 'required|string|max:255',
            'limit'         => 'nullable|numeric|min:0',
            'isActive'      => 'boolean',
        ];
    }

    protected $validationAttributes = [
        'type'          => 'Type',
        'name'          => 'Name',
        'nameInPayslip' => 'Name in Payslip',
        'limit'         => 'Limit',
    ];

    // Reset pagination when tab changes
    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->type      = $this->activeTab; // pre-select current tab's type
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record               = SalaryComponentModel::findOrFail($id);
        $this->editingId      = $id;
        $this->type           = $record->type;
        $this->name           = $record->name;
        $this->nameInPayslip  = $record->name_in_payslip;
        $this->limit          = $record->limit;
        $this->isActive       = $record->is_active;
        $this->isSystemRecord = $record->is_system;
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::first(['id'])?->id;

        $data = [
            'organisation_id' => $orgId,
            'type'            => $this->type,
            'name'            => $this->name,
            'name_in_payslip' => $this->nameInPayslip,
            'limit'           => $this->limit ?: null,
            'is_active'       => $this->isActive,
        ];

        try {
            DB::transaction(function () use ($data) {
                if ($this->editingId) {
                    $record = SalaryComponentModel::findOrFail($this->editingId);

                    // System records: only allow editing name_in_payslip, limit, is_active
                    if (strtolower($record->name) === 'basic') {
                        $record->update([
                            'name_in_payslip' => $this->nameInPayslip,
                            'limit'           => $this->limit ?: null,
                            'is_active'       => true,  //force active — can never be false
                        ]);
                    } else {
                        $record->update($data);
                    }
                } else {
                    // is_active is always true if "Basic".
                    if (strtolower($this->name) === 'basic') {
                        $data['is_active'] = true;
                    }

                    SalaryComponentModel::create($data);
                }
            });
             $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Salary component could not be saved.');
            \Log::error('Salary component save error: ' . $e->getMessage());
            return;
        }
    }

    public function confirmDelete(int $id): void
    {
        $record = SalaryComponentModel::findOrFail($id);

        // System records or "Basic" names cannot be deleted
        if ($record->is_system || strtolower($record->name) === 'basic') {
            return;
        }

        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            try {
                DB::transaction(function () {
                    $record = SalaryComponentModel::findOrFail($this->confirmDeleteId);

                    // System records or "Basic" names cannot be deleted
                    if ($record->is_system || strtolower($record->name) === 'basic') {
                        session()->flash('error', 'This salary component cannot be deleted.');
                        return;
                    }

                    $record->delete();
                    session()->flash('success', 'Salary component deleted successfully.');
                });
                $this->confirmDeleteId = null;
            } catch (\Exception $e) {
                session()->flash('error', 'Something went wrong! Salary component could not be deleted.');
                \Log::error('Salary component delete error: ' . $e->getMessage());
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
        $this->type           = $this->activeTab;
        $this->name           = '';
        $this->nameInPayslip  = '';
        $this->limit          = null;
        $this->isActive       = true;
        $this->isSystemRecord = false;
        $this->resetValidation();
    }

    public function render()
    {
        $org = Organisation::first(['id']);

        $components = SalaryComponentModel::query()
            ->when(
                $org,
                fn($q) => $q->where('organisation_id', $org->id),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->where('type', $this->activeTab)
            ->orderByDesc('is_system')   // System records (Basic) float to top
            ->orderByRaw("CASE WHEN LOWER(name) = 'basic' THEN 0 ELSE 1 END") // System records (Basic) float to top
            ->orderBy('created_at', 'asc')
            ->paginate(10);


        // Fetch the base currency
        $baseCurrency = $org
            ? Currency::baseCurrencyFor($org->id)
            : null;

        return view('livewire.salary-component.salary-component', [
            'componentList' => $components,
            'baseCurrency'  => $baseCurrency,
        ]);
    }
}
