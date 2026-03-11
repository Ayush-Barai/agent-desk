<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

final class CategoryManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $editingId = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    public bool $is_active = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $category = Category::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->editingId ?: null)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        $this->validate($rules);

        if ($this->editingId !== '') {
            $category = Category::query()->findOrFail($this->editingId);
            abort_unless($user->can('update', $category), 403);
            $category->update([
                'name' => $this->name,
                'description' => $this->description !== '' ? $this->description : null,
                'is_active' => $this->is_active,
            ]);
        } else {
            abort_unless($user->can('create', Category::class), 403);
            Category::query()->create([
                'name' => $this->name,
                'description' => $this->description !== '' ? $this->description : null,
                'is_active' => $this->is_active,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function deleteCategory(string $id): void
    {
        /** @var User $user */
        $user = Auth::user();
        $category = Category::query()->findOrFail($id);
        abort_unless($user->can('delete', $category), 403);
        $category->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function getCategories(): LengthAwarePaginator
    {
        return Category::query()->orderBy('name')->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.category-manager', [
            'categories' => $this->getCategories(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = '';
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->resetValidation();
    }
}
