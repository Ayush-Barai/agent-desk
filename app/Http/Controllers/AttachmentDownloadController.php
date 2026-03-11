<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentDownloadController
{
    public function __invoke(TicketAttachment $attachment): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('view', $attachment), 403);

        abort_unless(
            Storage::disk($attachment->disk)->exists($attachment->storage_path),
            404,
        );

        return Storage::disk($attachment->disk)->download(
            $attachment->storage_path,
            $attachment->original_name,
        );
    }
}
