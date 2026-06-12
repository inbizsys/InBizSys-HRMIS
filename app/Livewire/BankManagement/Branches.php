<?php

namespace App\Livewire\BankManagement;

use App\Models\Bank;
use App\Models\BankBranch;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Branches extends Component
{
    use WithPagination;

    public Bank $bank;

    // ── List / Filter ───────────────────────────────────────────────
    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $filterStatus = 'all';

    // ── Branch Form Modal (inside branches page) ────────────────────
    public bool $showBranchModal = false;

    public ?int $editingBranchId = null;

    public string $branchCode = '';

    public string $branchName = '';

    public bool $branchIsActive = true;

    // ── Delete Confirm ───────────────────────────────────────────────
    public ?int $confirmDeleteBranchId = null;

    // ── Delete Bank Confirmation (from bank list page) ───────────────
    public ?int $confirmDeleteBankId = null;

    public function mount($bankId): void
    {
        $this->bank = Bank::findOrFail($bankId);
    }

    protected function rules(): array
    {
        return [];
    }

    // ── Lifecycle Hooks ─────────────────────────────────────────────
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BankBranch::where('bank_id', $this->bank->id)
            ->when(
                $this->search,
                fn ($q) => $q->where('branch_name', 'like', '%'.$this->search.'%')
                    ->orWhere('branch_code', 'like', '%'.$this->search.'%')
            )
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('branch_code');

        return view('livewire.bank-management.branches', [
            'branches' => $query->paginate(10),
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // Branch CRUD
    // ════════════════════════════════════════════════════════════════

    public function openBranchModal(): void
    {
        $this->resetBranchForm();
        $this->showBranchModal = true;
    }

    public function editBranch(int $branchId): void
    {
        $branch = BankBranch::findOrFail($branchId);

        $this->editingBranchId = $branch->id;
        $this->branchCode = $branch->branch_code;
        $this->branchName = $branch->branch_name;
        $this->branchIsActive = $branch->is_active;
        $this->showBranchModal = true;
    }

    public function saveBranch(): void
    {
        $branchCode = strtoupper(trim($this->branchCode));

        $this->validate([
            'branchCode' => [
                'required',
                'max:20',
                function ($attribute, $value, $fail) use ($branchCode) {
                    $exists = BankBranch::where('bank_id', $this->bank->id)
                        ->whereRaw('UPPER(branch_code) = ?', [$branchCode])
                        ->when($this->editingBranchId, fn ($q) => $q->where('id', '!=', $this->editingBranchId))
                        // ->withTrashed()
                        ->exists();

                    if ($exists) {
                        $fail('This branch code already exists for this bank.');
                    }
                },
            ],
            'branchName' => [
                'required',
                'max:255',
            ],
        ], [
            'branchCode.required' => 'Branch code is required.',
            'branchName.required' => 'Branch name is required.',
        ]);

        $data = [
            'bank_id' => $this->bank->id,
            'branch_code' => $branchCode,
            'branch_name' => trim($this->branchName),
            'is_active' => $this->branchIsActive,
        ];

        try {
            DB::transaction(function () use ($data) {

                if ($this->editingBranchId) {
                    BankBranch::findOrFail($this->editingBranchId)->update($data);
                    $message = 'Branch updated successfully.';
                } else {
                    BankBranch::create($data);
                    $message = 'Branch added successfully.';
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Branch could not be saved.');
            \Log::error('Branch save error: '.$e->getMessage());
        }

        $this->closeBranchModal();
        $this->resetPage();
    }

    public function confirmDeleteBranch(int $branchId): void
    {
        $this->confirmDeleteBranchId = $branchId;
    }

    public function deleteBranch(): void
    {
        if ($this->confirmDeleteBranchId) {
            BankBranch::findOrFail($this->confirmDeleteBranchId)->delete();
            session()->flash('success', 'Branch deleted successfully.');
        }

        $this->confirmDeleteBranchId = null;
    }

    public function cancelDeleteBranch(): void
    {
        $this->confirmDeleteBranchId = null;
    }

    public function closeBranchModal(): void
    {
        $this->showBranchModal = false;
        $this->resetBranchForm();
    }

    private function resetBranchForm(): void
    {
        $this->editingBranchId = null;
        $this->branchCode = '';
        $this->branchName = '';
        $this->branchIsActive = true;
        $this->resetErrorBag(['branchCode', 'branchName']);
    }

    // ════════════════════════════════════════════════════════════════
    // Bank Navigation
    // ════════════════════════════════════════════════════════════════

    public function confirmDeleteBank(): void
    {
        $this->confirmDeleteBankId = $this->bank->id;
    }

    public function deleteBankAndRedirect(): void
    {
        if ($this->confirmDeleteBankId) {
            $bank = Bank::findOrFail($this->confirmDeleteBankId);
            $bank->branches()->delete();
            $bank->delete();

            session()->flash('success', 'Bank and all its branches deleted successfully.');

            $this->redirectRoute('configurations.banks.index', navigate: true);
        }

        $this->confirmDeleteBankId = null;
    }

    public function cancelDeleteBank(): void
    {
        $this->confirmDeleteBankId = null;
    }

    public function goBack(): void
    {
        // Use redirect()->route() - NO return statement here
        $this->redirectRoute('configurations.banks.index', navigate: true);
    }
}
