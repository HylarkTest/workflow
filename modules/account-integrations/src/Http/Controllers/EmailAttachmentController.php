<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

class EmailAttachmentController extends Controller
{
    public function show(int $accountId, string $mailboxId, string $emailId, string $attachmentId): Response|StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        /** @var \AccountIntegrations\Models\IntegrationAccount $account */
        $account = $user->integrationAccounts()->findOrFail($accountId);

        try {
            $attachment = $account->getAttachment(base64_decode($emailId), base64_decode($attachmentId), base64_decode($mailboxId));
        } catch (ResourceNotFoundException $e) {
            return response($e->getMessage(), 404);
        }

        return response()->streamDownload(function () use ($attachment) {
            if (\is_string($attachment->content)) {
                echo $attachment->content;
            } else {
                echo base64_decode($attachment->content->getContents(), true);
            }
        }, Str::ascii($attachment->name));
    }
}
