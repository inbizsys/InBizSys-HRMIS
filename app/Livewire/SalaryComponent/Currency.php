<?php

namespace App\Livewire\SalaryComponent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Currency as CurrencyModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class Currency extends Component
{
    use WithPagination;

    // Modal State
    public bool $showModal    = false;
    public $editingId         = null;

    // Form Fields
    public string $name           = '';
    public string $code           = '';
    public bool   $isBaseCurrency = false;

    // Delete Confirm
    public $confirmDeleteId = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        $orgId = Organisation::first(['id'])?->id;

        $codeUnique = $this->editingId
            ? "required|string|max:10|unique:currencies,code,{$this->editingId},id,organisation_id,{$orgId}"
            : "required|string|max:10|unique:currencies,code,NULL,id,organisation_id,{$orgId}";

        return [
            'name'           => 'required|string|max:255',
            'code'           => $codeUnique,
            'isBaseCurrency' => 'boolean',
        ];
    }

    protected $validationAttributes = [
        'name' => 'Currency Name',
        'code' => 'Currency Code',
    ];

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record               = CurrencyModel::findOrFail($id);
        $this->editingId      = $id;
        $this->name           = $record->name;
        $this->code           = $record->code;
        $this->isBaseCurrency = $record->is_base_currency;
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::first(['id'])?->id;

        // If is_base_currency = true then unset base currency first
        if ($this->isBaseCurrency) {
            CurrencyModel::query()
                ->where('organisation_id', $orgId)
                ->where('is_base_currency', true)
                ->when($this->editingId !== null, fn($q) => $q->where('id', '!=', $this->editingId))
                ->each(fn($c) => $c->update(['is_base_currency' => false]));
        }

        $data = [
            'organisation_id' => $orgId,
            'name'            => $this->name,
            'code'            => strtoupper($this->code),
            'is_base_currency' => $this->isBaseCurrency,
        ];

        try {
            DB::transaction(function () use ($data) {

                if ($this->editingId) {
                    CurrencyModel::findOrFail($this->editingId)->update($data);
                    $message = 'Currency updated successfully.';
                } else {
                    CurrencyModel::create($data);
                    $message = 'Currency added successfully.';
                }
                session()->flash('success', $message);
            });

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Currency could not be saved.');
            \Log::error('Currency save error: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        // Base currency delete කරන්න දෙන්නෑ
        $record = CurrencyModel::findOrFail($id);
        if ($record->is_base_currency) {
            return;
        }
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            try {
                DB::transaction(function () {
                    $record = CurrencyModel::findOrFail($this->confirmDeleteId);
                    if (! $record->is_base_currency) {
                        $record->delete();
                    }
                });
                session()->flash('success', 'Currency deleted successfully.');

                $this->confirmDeleteId = null;
            } catch (\Exception $e) {
                session()->flash('error', 'Something went wrong! Currency could not be deleted.');
                \Log::error('Currency delete error: ' . $e->getMessage());
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
        $this->code           = '';
        $this->isBaseCurrency = false;
        $this->resetValidation();
    }

    public function render()
    {
        $org = Organisation::first(['id']);

        $currencies = CurrencyModel::query()
            ->when(
                $org,
                fn($q) => $q->where('organisation_id', $org->id),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->orderByDesc('is_base_currency')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.salary-component.currency', [
            'currencyList' => $currencies,
        ]);
    }
}
