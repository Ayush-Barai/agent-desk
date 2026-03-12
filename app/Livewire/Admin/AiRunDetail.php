<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AiRun;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class AiRunDetail extends Component
{
    public string $aiRunId = '';

    public function mount(AiRun $aiRun): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->can('view', $aiRun), 403);

        $this->aiRunId = $aiRun->id;
    }

    public function getAiRun(): AiRun
    {
        $aiRun = AiRun::query()
            ->with(['ticket', 'initiator'])
            ->findOrFail($this->aiRunId);

        /** @var AiRun $aiRun */
        return $aiRun;
    }

    public function render(): View
    {
        return view('livewire.admin.ai-run-detail', [
            'aiRun' => $this->getAiRun(),
        ]);
    }
}
