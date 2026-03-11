<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\CreateAuditLog;
use App\Models\SupportTargetConfig;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class SupportTargetSettings extends Component
{
    public string $configId = '';

    public int $first_response_hours = 24;

    public int $resolution_hours = 72;

    public bool $saved = false;

    public function mount(): void
    {
        $config = SupportTargetConfig::query()->first();

        if ($config instanceof SupportTargetConfig) {
            $this->configId = $config->id;
            $this->first_response_hours = $config->first_response_hours;
            $this->resolution_hours = $config->resolution_hours;
        }
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->isAdmin(), 403);

        $this->validate([
            'first_response_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'resolution_hours' => ['required', 'integer', 'min:1', 'max:2160'],
        ]);

        $audit = new CreateAuditLog();

        if ($this->configId !== '') {
            $config = SupportTargetConfig::query()->findOrFail($this->configId);
            $oldValues = [
                'first_response_hours' => $config->first_response_hours,
                'resolution_hours' => $config->resolution_hours,
            ];
            $config->update([
                'first_response_hours' => $this->first_response_hours,
                'resolution_hours' => $this->resolution_hours,
            ]);
            $audit->execute(
                action: 'config_updated',
                actor: $user,
                auditable: $config,
                oldValues: $oldValues,
                newValues: [
                    'first_response_hours' => $this->first_response_hours,
                    'resolution_hours' => $this->resolution_hours,
                ],
            );
        } else {
            $config = SupportTargetConfig::query()->create([
                'first_response_hours' => $this->first_response_hours,
                'resolution_hours' => $this->resolution_hours,
            ]);
            $this->configId = $config->id;
            $audit->execute(
                action: 'config_created',
                actor: $user,
                auditable: $config,
                newValues: [
                    'first_response_hours' => $this->first_response_hours,
                    'resolution_hours' => $this->resolution_hours,
                ],
            );
        }

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.admin.support-target-settings');
    }
}
