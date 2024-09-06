<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use AccountIntegrations\Core\Emails\Email;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Email as SymfonyMessage;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

/**
 * @phpstan-import-type AttachmentInfo from \AccountIntegrations\Core\Emails\Attachment
 */
trait SendsSmtpEmails
{
    /**
     * @param  AttachmentInfo[]  $attachments
     */
    public function smtpSend(Email $email, array $attachments = []): Email
    {
        $mailer = $this->createMailer($this->account->account_name);

        $mailable = $this->buildMailableFromEmail($email, $attachments);

        $sentEmail = $mailable->send($mailer);

        $email->id = $sentEmail?->getMessageId();

        return $email;
    }

    protected function createDsn(): Dsn
    {
        throw new \Exception('This method must be overridden');
    }

    protected function createMailer(string $name): Mailer
    {
        if (config('account-integrations.trap-emails')) {
            return Mail::mailer();
        }

        $factory = new EsmtpTransportFactory;

        /** @var \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport $transport */
        $transport = $factory->create($this->createDsn());

        $mailer = new \Illuminate\Mail\Mailer(
            $name,
            resolve('view'),
            $transport,
            resolve('events'),
        );

        if (app()->bound('queue')) {
            $mailer->setQueue(resolve('queue'));
        }

        return $mailer;
    }

    /**
     * @param  AttachmentInfo[]  $attachments
     */
    protected function buildMailableFromEmail(Email $email, array $attachments = []): Mailable
    {
        $mailable = new class extends Mailable
        {
            public function build(): self
            {
                return $this;
            }

            public function prepare(\Illuminate\Mail\Message $message): self
            {
                return $this->buildFrom($message)
                    ->buildRecipients($message)
                    ->buildSubject($message)
                    ->buildTags($message)
                    ->buildMetadata($message)
                    ->runCallbacks($message)
                    ->buildAttachments($message);
            }

            public function getView(): string|array
            {
                return $this->buildView();
            }
        };

        if ($email->from) {
            $mailable->from($email->from);
        }
        if ($email->to) {
            $mailable->to($email->to);
        }
        if ($email->cc ?? null) {
            $mailable->cc($email->cc);
        }
        if ($email->bcc ?? null) {
            $mailable->bcc($email->bcc);
        }
        $mailable->subject($email->subject ?: 'No subject');
        if ($email->html ?? null) {
            $mailable->html($email->html);
        }
        if ($email->text ?? null) {
            $mailable->text($email->text);
        }
        if ($attachments) {
            foreach ($attachments as $attachment) {
                if (! $attachment['isInline']) {
                    $file = $attachment['file'];
                    $name = $attachment['name'] ?? $file->getClientOriginalName();
                    $mailable->attachData(
                        $file->getContent(),
                        $name,
                        ['mime' => $file->getMimeType()]
                    );
                } elseif ($attachment['contentId'] ?? null) {
                    $mailable->withSymfonyMessage(function (SymfonyMessage $message) use ($attachment) {
                        $file = $attachment['file'];
                        $message->embedFromPath($file->path(), $attachment['contentId']);
                    });
                }
            }
        }

        return $mailable;
    }
}
