<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Illuminate\Http\UploadedFile;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Core\Emails\Attachment;
use AccountIntegrations\Models\IntegrationAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use AccountIntegrations\Core\Emails\Repositories\MicrosoftGraphEmailRepository;

uses(RefreshDatabase::class);

function dummyMailbox(IntegrationAccount $integration): Mailbox
{
    return new Mailbox([
        'id' => 'def456',
        'name' => 'My Mailbox',
    ], $integration);
}

function dummyAttachment(UploadedFile $file, IntegrationAccount $integration): Attachment
{
    return new Attachment([
        'id' => 'abc',
        'contentId' => 'abc',
        'name' => 'file.png',
        'content' => $file->getContent(),
        'fileType' => $file->getMimeType(),
        'link' => route('email-attachment-download-link', ['accountId' => $integration->id, 'mailboxId' => 'def', 'emailId' => '123', 'attachmentId' => 'abc']),
        'isInline' => true,
    ]);
}

function mockEmailRepository($cb): void
{
    app()->bind(
        MicrosoftGraphEmailRepository::class,
        fn () => \Mockery::mock(MicrosoftGraphEmailRepository::class, fn (MockInterface $mock) => $cb($mock))
    );
}

test('a user can fetch their mailboxes', function () {
    $user = createUser();
    $integration = createIntegrationAccount($user);
    mockEmailRepository(fn ($mock) => $mock->shouldReceive('getMailboxes')
        ->andReturn(collect([dummyMailbox($integration)])));

    $this->be($user)->assertGraphQL([
        "mailboxes(sourceId: \"$integration->global_id\")" => [
            'data' => [
                ['id' => base64_encode('def456'), 'name' => 'My Mailbox'],
            ],
        ],
    ]);
});

test('a user can fetch attachments', function () {
    $user = createUser();
    $integration = createIntegrationAccount($user);

    $file = UploadedFile::fake()->image('file.png');

    mockEmailRepository(fn ($mock) => $mock->shouldReceive('getAttachment')
        ->andReturn(dummyAttachment($file, $integration)));

    $this->be($user)->get(route('email-attachment-download-link', ['accountId' => $integration->id, 'mailboxId' => 'def', 'emailId' => '123', 'attachmentId' => 'abc']))
        ->assertSuccessful()
        ->assertHeader('Content-Disposition', 'attachment; filename=file.png');
});

test('a user can send an email with inline attachment links', function () {
    $user = createUser();
    $integration = createIntegrationAccount($user);

    $file = UploadedFile::fake()->image('file.png');

    $mailbox = dummyMailbox($integration);
    $attachment = dummyAttachment($file, $integration);
    $email = new Email([
        'id' => 'abc',
        'subject' => 'Hello',
        'html' => '<img src="cid:abc">',
        'hasAttachments' => false,
        'attachments' => [[
            'name' => 'file.png',
            'isInline' => true,
            'contentId' => 'abc',
            'link' => route('email-attachment-download-link', ['accountId' => $integration->id, 'mailboxId' => 'def', 'emailId' => '123', 'attachmentId' => 'abc']),
        ]],
    ], $mailbox, $integration);

    mockEmailRepository(function ($mock) use ($attachment, $email) {
        $mock->shouldReceive('getAttachment')
            ->andReturn($attachment);
        $mock->shouldReceive('saveDraft')
            ->andReturn($email);
    });

    $this->be($user)->assertGraphQLMutation(
        'createEmail',
        ['input: CreateEmailInput!' => [
            'sourceId' => $integration->global_id,
            'subject' => 'Hello',
            'html' => '<img src="cid:abc">',
            'isDraft' => true,
            'attachments' => [[
                'contentId' => 'abc',
                'isInline' => true,
                'link' => route('email-attachment-download-link', ['accountId' => $integration->id, 'mailboxId' => 'def', 'emailId' => '123', 'attachmentId' => 'abc']),
            ]],
        ]],
    );
});
