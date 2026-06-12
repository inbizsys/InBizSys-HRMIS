<?php

namespace App\Livewire\SalaryComponent;

use Livewire\Component;
use App\Models\EpfEtfSetting;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class EpfEtf extends Component
{
    // Active tab: 'epf' | 'etf'
    public string $activeTab = 'epf';

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public string $fundType           = 'epf';
    public string $registrationNumber = '';
    public string $deductionCycle     = 'monthly';
    public string $employeeRate       = '';
    public string $employerRate       = '';
    public bool   $isActive           = true;

    // Delete Confirm State
    public $confirmDeleteId = null;

    // ──────────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────────

    protected function rules(): array
    {
        // ETF has no employee contribution — skip min validation for employee_rate when etf
        $employeeRateRule = $this->activeTab === 'etf'
            ? 'required|numeric|min:0|max:100'
            : 'required|numeric|min:0.01|max:100';

        return [
            'registrationNumber' => 'nullable|string|max:100',
            'deductionCycle'     => 'required|in:monthly,bi_monthly,quarterly',
            'employeeRate'       => $employeeRateRule,
            'employerRate'       => 'required|numeric|min:0.01|max:100',
            'isActive'           => 'boolean',
        ];
    }

    protected $validationAttributes = [
        'fundType'           => 'Fund Type',
        'registrationNumber' => 'Registration Number',
        'deductionCycle'     => 'Deduction Cycle',
        'employeeRate'       => 'Employee Contribution Rate',
        'employerRate'       => 'Employer Contribution Rate',
    ];

    // ──────────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────────

    public function updatedActiveTab(): void
    {
        // nothing extra needed — render() picks up the new tab
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ──────────────────────────────────────────────
    // Modal helpers
    // ──────────────────────────────────────────────

    public function openModal(): void
    {
        $this->resetForm();

        // Pre-fill statutory defaults based on active tab
        $this->fundType = $this->activeTab;
        $this->prefillDefaults($this->activeTab);

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record = EpfEtfSetting::findOrFail($id);

        $this->editingId          = $id;
        $this->fundType           = $record->fund_type;
        $this->registrationNumber = $record->registration_number ?? '';
        $this->deductionCycle     = $record->deduction_cycle;
        $this->employeeRate       = $record->employee_rate;
        $this->employerRate       = $record->employer_rate;
        $this->isActive           = $record->is_active;
        $this->showModal          = true;
    }

    public function save(): void
    {
        // Lock fund_type to the active tab — prevent any client-side tampering
        if (! $this->editingId) {
            $this->fundType = $this->activeTab;
        }

        $this->validate();

        $orgId = Organisation::first(['id'])?->id;

        // Manual duplicate check — show a clear validation error on the form
        $duplicateExists = EpfEtfSetting::where('organisation_id', $orgId)
            ->where('fund_type', $this->fundType)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($duplicateExists) {
            $label = strtoupper($this->fundType);
            $this->addError(
                'registrationNumber',
                "{$label} settings already exist for this organisation. Only one {$label} configuration is allowed."
            );
            return;
        }

        $orgId = Organisation::first(['id'])?->id;

        $data = [
            'organisation_id'     => $orgId,
            'fund_type'           => $this->editingId
                                        ? $this->fundType          // keep original on edit
                                        : $this->activeTab,        // always use active tab on create
            'registration_number' => $this->registrationNumber ?: null,
            'deduction_cycle'     => $this->deductionCycle,
            'employee_rate'       => $this->employeeRate,
            'employer_rate'       => $this->employerRate,
            'is_active'           => $this->isActive,
        ];

        try {
            DB::transaction(function () use ($data) {
                if ($this->editingId) {
                    EpfEtfSetting::findOrFail($this->editingId)->update($data);
                } else {
                    EpfEtfSetting::create($data);
                }
            });

            $label = strtoupper($this->editingId ? $this->fundType : $this->activeTab);
            $this->closeModal();
            session()->flash('success', $label . ' settings saved successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Settings could not be saved.');
            \Log::error('EPF/ETF save error: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if (! $this->confirmDeleteId) {
            return;
        }

        try {
            DB::transaction(function () {
                EpfEtfSetting::findOrFail($this->confirmDeleteId)->delete();
            });

            session()->flash('success', 'Settings deleted successfully.');
            $this->confirmDeleteId = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Settings could not be deleted.');
            \Log::error('EPF/ETF delete error: ' . $e->getMessage());
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

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingId          = null;
        $this->fundType           = $this->activeTab;
        $this->registrationNumber = '';
        $this->deductionCycle     = 'monthly';
        $this->employeeRate       = '';
        $this->employerRate       = '';
        $this->isActive           = true;
        $this->resetValidation();
    }

    private function prefillDefaults(string $fundType): void
    {
        if ($fundType === 'epf') {
            $this->employeeRate = EpfEtfSetting::EPF_EMPLOYEE_RATE;
            $this->employerRate = EpfEtfSetting::EPF_EMPLOYER_RATE;
        } else {
            $this->employeeRate = EpfEtfSetting::ETF_EMPLOYEE_RATE;
            $this->employerRate = EpfEtfSetting::ETF_EMPLOYER_RATE;
        }
    }


    // Render
    // ──────────────────────────────────────────────

    public function render()
    {
        $org = Organisation::first(['id']);

        $settings = EpfEtfSetting::query()
            ->when(
                $org,
                fn($q) => $q->where('organisation_id', $org->id),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->where('fund_type', $this->activeTab)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.salary-component.epf-etf', [
            'settingsList' => $settings,
        ]);
    }
}
