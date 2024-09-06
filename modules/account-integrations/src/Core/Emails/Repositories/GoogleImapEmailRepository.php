<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Webklex\PHPIMAP\Folder;
use Illuminate\Support\Collection;
use Webklex\PHPIMAP\ClientManager;
use Symfony\Component\Mailer\Transport\Dsn;
use AccountIntegrations\Core\Emails\Mailbox;
use AccountIntegrations\Models\IntegrationAccount;
use Webklex\PHPIMAP\Exceptions\FolderFetchingException;

class GoogleImapEmailRepository extends ImapRepository
{
    protected static array $commonFolderOrder = [
        'inbox',
        '[gmail]/drafts',
        '{FOLDERS}',
        '[gmail]/starred',
        '[gmail]/important',
        '[gmail]/sent mail',
        '[gmail]/archive',
        '[gmail]/all mail',
        '[gmail]/bin',
        '[gmail]/spam',
    ];

    protected static array $collapsedFolders = [
        '[gmail]/sent mail',
        '[gmail]/archive',
        '[gmail]/bin',
        '[gmail]/all mail',
        '[gmail]/spam',
    ];

    protected static array $hiddenFolders = [
        '[gmail]',
    ];

    protected static array $protectedFolders = [
        'inbox',
        '[gmail]/drafts',
        '[gmail]/starred',
        '[gmail]/important',
        '[gmail]/sent mail',
        '[gmail]/archive',
        '[gmail]/all mail',
        '[gmail]/bin',
        '[gmail]/spam',
    ];

    public function __construct(IntegrationAccount $account)
    {
        $account->refreshToken();

        $clientManager = new ClientManager([]);

        $client = $clientManager->make([
            'host' => 'imap.gmail.com',
            'port' => 993,
            'encryption' => 'ssl',
            'validate_cert' => true,
            'authentication' => 'oauth',
            'protocol' => 'imap',
            'username' => $account->account_name,
            'password' => $account->token,
        ]);

        parent::__construct($client, $account);
    }

    public function getMailboxes(): Collection
    {
        $mailboxes = Collection::make();

        $connection = $this->imapClient->getConnection();
        $items = $connection->folders('', '*')->validatedData();

        if (! empty($items)) {
            foreach ($items as $name => $item) {
                $folder = new Folder($this->imapClient, $name, $item['delimiter'], $item['flags']);
                // Gmail uses different case for \NoSelect flag. Follow this
                // issue to see if it can be removed: https://github.com/Webklex/php-imap/issues/469
                if (! \in_array('\Noselect', $item['flags'], true) && ! $folder->no_select) {
                    $mailboxes->push($this->imapFolderToMailbox($folder));
                }
            }
        } else {
            throw new FolderFetchingException('failed to fetch any folders');
        }

        $folderIndex = (string) array_search('{FOLDERS}', static::$commonFolderOrder, true);

        return $mailboxes
            ->filter(fn (Mailbox $mailbox) => $mailbox->name && ! \in_array(mb_strtolower($mailbox->path()), static::$hiddenFolders, true))
            ->sortBy(function (Mailbox $mailbox) use ($folderIndex) {
                $order = array_search(mb_strtolower($mailbox->path()), static::$commonFolderOrder, true);

                if ($order === false) {
                    return $folderIndex.$mailbox->name;
                }

                return (string) $order;
            });
    }

    protected function imapFolderToMailbox(Folder $folder): Mailbox
    {
        $mailbox = parent::imapFolderToMailbox($folder);

        $mailbox->isCollapsed = \in_array(mb_strtolower($mailbox->path()), static::$collapsedFolders, true);

        if ($mailbox->name === 'INBOX') {
            $mailbox->name = 'Inbox';
        } else {
            $mailbox->name = (string) preg_replace('/\[Gmail]\/?/', '', $mailbox->name);
        }
        $mailbox->isDefault = \in_array(mb_strtolower((string) $mailbox->name), static::$protectedFolders, true);

        return $mailbox;
    }

    protected function createDsn(): Dsn
    {
        return new Dsn(
            'smtp',
            'smtp.gmail.com',
            $this->account->account_name,
            $this->account->token,
            465,
        );
    }

    protected function getDraftMailboxId(): string
    {
        return '[Gmail]/Drafts';
    }

    protected function getSentMailboxId(): string
    {
        return '[Gmail]/Sent Mail';
    }

    protected function getAllMailboxId(): string
    {
        return '[Gmail]/All Mail';
    }

    public function getEmailsSummary(?string $mailboxId = null, array $options = []): Collection
    {
        if (! $mailboxId) {
            $mailboxId = $this->getAllMailboxId();
        }

        return parent::getEmailsSummary($mailboxId, $options);
    }
}
