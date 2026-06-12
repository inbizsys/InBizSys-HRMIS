<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TaxSlab as TaxSlabModel;
use App\Models\Currency as CurrencyModel;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class TaxSlab extends Component
{
    use WithPagination;

    // Modal State
    public bool $showModal = false;
    public $editingId = null;

    // Form Fields
    public $min_amount    = '';
    public $max_amount    = '';
    public $tax_percentage;
    public bool $is_relief = false;   // true = Relief from Tax slab (no percentage)
    public string $description = '';

    // Delete Confirm State
    public $confirmDeleteId = null;

    protected $paginationTheme = 'tailwind';

    // ──────────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'min_amount'       => 'nullable|numeric|min:0',
            'max_amount'       => 'nullable|numeric|gt:min_amount',
            'is_relief'        => 'boolean',
            'tax_percentage'   => $this->is_relief ? 'nullable' : 'required|numeric|min:0|max:100',
            'description'      => 'nullable|string|max:500',
        ];
    }

    protected $validationAttributes = [
        'min_amount'       => 'Minimum Amount',
        'max_amount'       => 'Maximum Amount',
        'tax_percentage'   => 'Tax Percentage',
    ];

    protected $messages = [
        'max_amount.gt' => 'Maximum amount must be greater than the minimum amount.',
    ];

    // ──────────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────────

    public function updatedIsRelief($value): void
    {
        if ($value) {
            $this->tax_percentage = null;
        }
    }

    // ──────────────────────────────────────────────
    // Modal Controls
    // ──────────────────────────────────────────────

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $record                  = TaxSlabModel::findOrFail($id);
        $this->editingId         = $id;
        $this->min_amount        = $record->min_amount;
        $this->max_amount        = $record->max_amount;
        $this->tax_percentage    = $record->tax_percentage;
        $this->description       = $record->description ?? '';
        $this->is_relief         = is_null($record->tax_percentage);
        $this->showModal         = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // ──────────────────────────────────────────────
    // CRUD
    // ──────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        $orgId = Organisation::first(['id'])?->id;

        // Determine the slab_order
        $slabOrder = $this->editingId
            ? TaxSlabModel::findOrFail($this->editingId)->slab_order
            : (TaxSlabModel::where('organisation_id', $orgId)->max('slab_order') ?? 0) + 1;

        $data = [
            'organisation_id'  => $orgId,
            'slab_order'       => $slabOrder,
            'min_amount'       => $this->min_amount !== '' ? $this->min_amount : null,
            'max_amount'       => $this->max_amount !== '' ? $this->max_amount : null,
            'tax_percentage'   => $this->is_relief ? null : $this->tax_percentage,
            'description'      => $this->description ?: null,
        ];

        try {
            DB::transaction(function () use ($data) {
                if ($this->editingId) {
                    TaxSlabModel::findOrFail($this->editingId)->update($data);
                    $message = 'Tax slab updated successfully.';
                } else {
                    TaxSlabModel::create($data);
                    $message = 'Tax slab added successfully.';
                }
                session()->flash('success', $message);
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong! Tax slab could not be saved.');
            \Log::error('TaxSlab save error: ' . $e->getMessage());
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
            $slabId = $this->confirmDeleteId;

            try {
                DB::transaction(function () use ($slabId) {
                    $slab = TaxSlabModel::findOrFail($slabId);
                    $deletedOrder = $slab->slab_order;
                    $orgId = $slab->organisation_id;

                    $slab->delete();

                    // Re-sequence slab_order after deletion
                    TaxSlabModel::where('organisation_id', $orgId)
                        ->where('slab_order', '>', $deletedOrder)
                        ->orderBy('slab_order')
                        ->get()
                        ->each(function ($s, $index) use ($deletedOrder) {
                            $s->update(['slab_order' => $deletedOrder + $index]);
                        });

                    session()->flash('success', 'Tax slab deleted successfully.');
                });

                $this->confirmDeleteId = null;

            } catch (\Exception $e) {
                session()->flash('error', 'Something went wrong! Tax slab could not be deleted.');
                \Log::error('TaxSlab delete error: ' . $e->getMessage());
            }
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    // ──────────────────────────────────────────────
    // Reorder helpers
    // ──────────────────────────────────────────────

    public function moveUp(int $id): void
    {
        $this->swapSlabOrder($id, 'up');
    }

    public function moveDown(int $id): void
    {
        $this->swapSlabOrder($id, 'down');
    }

    private function swapSlabOrder(int $id, string $direction): void
    {
        $slab  = TaxSlabModel::findOrFail($id);
        $orgId = $slab->organisation_id;

        $sibling = TaxSlabModel::where('organisation_id', $orgId)
            ->when($direction === 'up',
                fn($q) => $q->where('slab_order', '<', $slab->slab_order)->orderByDesc('slab_order'),
                fn($q) => $q->where('slab_order', '>', $slab->slab_order)->orderBy('slab_order')
            )
            ->first();

        if ($sibling) {
            DB::transaction(function () use ($slab, $sibling) {
                [$slab->slab_order, $sibling->slab_order] = [$sibling->slab_order, $slab->slab_order];
                $slab->save();
                $sibling->save();
            });
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingId        = null;
        $this->min_amount       = '';
        $this->max_amount       = '';
        $this->tax_percentage   = '';
        $this->description      = '';
        $this->is_relief        = false;
        $this->resetValidation();
    }

    // ──────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────

    public function render()
    {
        $org = Organisation::first(['id']);

        $taxSlabs = TaxSlabModel::query()
            ->when(
                $org,
                fn($q) => $q->where('organisation_id', $org->id),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->ordered()
            ->paginate(15);

        // Base currency symbol — view එකේ labels සඳහා (modal input labels ආදිය)
        // Table cells වල range_label / tax_label accessors Model level එකෙන් handle කරනවා
        $currencySymbol = $org
            ? (CurrencyModel::baseCurrencyFor($org->id)?->code ?? 'Rs.')
            : 'Rs.';

        return view('livewire.organisation.tax-slab', [
            'taxSlabList'    => $taxSlabs,
            'currencySymbol' => $currencySymbol,
        ]);
    }
}
