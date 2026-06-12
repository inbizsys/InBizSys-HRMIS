<?php

namespace App\Livewire\BankManagement;

use App\Models\Bank;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Banks extends Component
{
    use WithPagination;

    // ── List / Filter ───────────────────────────────────────────────
    public string $search = '';

    public string $filterStatus = 'active';

    // ── Bank Modal ──────────────────────────────────────────────────
    public bool $showBankModal = false;

    public ?int $editingBankId = null;

    public string $bankCode = '';

    public string $bankName = '';

    public bool $bankIsActive = true;

    // ── Delete Confirm ───────────────────────────────────────────────
    public ?int $confirmDeleteBankId = null;

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

    // ════════════════════════════════════════════════════════════════
    // Computed / Render
    // ════════════════════════════════════════════════════════════════

    public function render()
    {
        $query = Bank::query()
            ->withCount('branches')
            ->when(
                $this->search,
                fn ($q) => $q->where('bank_name', 'like', '%'.$this->search.'%')
                    ->orWhere('bank_code', 'like', '%'.$this->search.'%')
            )
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('bank_name');

        return view('livewire.bank-management.banks', [
            'bankList' => $query->paginate(10),
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // Bank CRUD
    // ════════════════════════════════════════════════════════════════

    public function openBankModal(): void
    {
        $this->resetBankForm();
        $this->showBankModal = true;
    }

    public function editBank(int $bankId): void
    {
        $bank = Bank::findOrFail($bankId);

        $this->editingBankId = $bank->id;
        $this->bankCode = $bank->bank_code;
        $this->bankName = $bank->bank_name;
        $this->bankIsActive = $bank->is_active;
        $this->showBankModal = true;
    }

    public function saveBank(): void
    {
        $bankCode = strtoupper(trim($this->bankCode));

        $this->validate([
            'bankCode' => [
                'required',
                'max:20',
                function ($attribute, $value, $fail) use ($bankCode) {
                    $exists = Bank::whereRaw('UPPER(bank_code) = ?', [$bankCode])
                        ->when($this->editingBankId, fn ($q) => $q->where('id', '!=', $this->editingBankId))
                        // ->withTrashed()
                        ->exists();

                    if ($exists) {
                        $fail('This bank code already exists. Please use a different code.');
                    }
                },
            ],
            'bankName' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Bank::whereRaw('LOWER(bank_name) = ?', [strtolower(trim($value))])
                        ->when($this->editingBankId, fn ($q) => $q->where('id', '!=', $this->editingBankId))
                        // ->withTrashed()
                        ->exists();

                    if ($exists) {
                        $fail('A bank with this name already exists.');
                    }
                },
            ],
        ], [
            'bankCode.required' => 'Bank code is required.',
            'bankName.required' => 'Bank name is required.',
        ]);

        $data = [
            'bank_code' => $bankCode,
            'bank_name' => trim($this->bankName),
            'is_active' => $this->bankIsActive,
        ];

        try {
            DB::transaction(function () use ($data) {

                if ($this->editingBankId) {
                    Bank::findOrFail($this->editingBankId)->update($data);
                    $message = 'Bank updated successfully.';
                } else {
                    Bank::create($data);
                    $message = 'Bank added successfully.';
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Bank could not be saved.');
            \Log::error('Bank save error: '.$e->getMessage());

            return;
        }

        $this->closeBankModal();
        $this->resetPage();
    }

    public function confirmDeleteBank(int $bankId): void
    {
        $this->confirmDeleteBankId = $bankId;
    }

    public function deleteBank(): void
    {
        if ($this->confirmDeleteBankId) {
            try {
                DB::transaction(function () {
                    $bank = Bank::findOrFail($this->confirmDeleteBankId);
                    $bank->branches()->delete();  // soft-delete branches too
                    $bank->delete();
                    session()->flash('success', 'Bank deleted successfully.');
                });
            } catch (\Exception $e) {
                session()->flash('error', 'Something went wrong! Bank could not be deleted.');
                \Log::error('Bank delete error: '.$e->getMessage());
            }
        }

        $this->confirmDeleteBankId = null;
    }

    public function cancelDeleteBank(): void
    {
        $this->confirmDeleteBankId = null;
    }

    public function closeBankModal(): void
    {
        $this->showBankModal = false;
        $this->resetBankForm();
    }

    private function resetBankForm(): void
    {
        $this->editingBankId = null;
        $this->bankCode = '';
        $this->bankName = '';
        $this->bankIsActive = true;
        $this->resetErrorBag();
    }
}
