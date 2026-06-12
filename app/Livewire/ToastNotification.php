<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class ToastNotification extends Component
{
    public array $toasts = [];
    private int $nextId = 0;

    /**
     * Listen for the 'notify' event dispatched from any Livewire component.
     *
     * Usage from any Livewire component:
     *   $this->dispatch('notify', type: 'success', message: 'Saved!');
     *   $this->dispatch('notify', type: 'error',   message: 'Something went wrong.');
     *   $this->dispatch('notify', type: 'warning', message: 'Please check inputs.');
     *   $this->dispatch('notify', type: 'info',    message: 'Record updated.');
     *
     * Supported types: success | error | warning | info
     */
    #[On('notify')]
    public function addToast(string $type, string $message): void
    {
        $this->toasts[] = [
            'id'      => ++$this->nextId,
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Called by the blade (via wire:click or Alpine) to manually dismiss a toast.
     */
    public function dismiss(int $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn($t) => $t['id'] !== $id)
        );
    }

    public function render()
    {
        return view('livewire.toast-notification');
    }
}
