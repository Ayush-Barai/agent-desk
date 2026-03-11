<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Macro;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

final class MacroManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $editingId = '';

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:10000')]
    public string $body = '';

    public bool $is_active = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $macro = Macro::query()->findOrFail($id);
        $this->editingId = $macro->id;
        $this->title = $macro->title;
        $this->body = $macro->body;
        $this->is_active = $macro->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'title' => ['required', 'string', 'max:255', Rule::unique('macros', 'title')->ignore($this->editingId ?: null)],
            'body' => ['required', 'string', 'max:10000'],
        ];

        $this->validate($rules);

        if ($this->editingId !== '') {
            $macro = Macro::query()->findOrFail($this->editingId);
            abort_unless($user->can('update', $macro), 403);
            $macro->update([
                'title' => $this->title,
                'body' => $this->body,
                'is_active' => $this->is_active,
            ]);
        } else {
            abort_unless($user->can('create', Macro::class), 403);
            Macro::query()->create([
                'title' => $this->title,
                'body' => $this->body,
                'is_active' => $this->is_active,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function deleteMacro(string $id): void
    {
        /** @var User $user */
        $user = Auth::user();
        $macro = Macro::query()->findOrFail($id);
        abort_unless($user->can('delete', $macro), 403);
        $macro->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * @return LengthAwarePaginator<int, Macro>
     */
    public function getMacros(): LengthAwarePaginator
    {
        return Macro::query()->orderBy('title')->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.macro-manager', [
            'macros' => $this->getMacros(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = '';
        $this->title = '';
        $this->body = '';
        $this->is_active = true;
        $this->resetValidation();
    }
}
