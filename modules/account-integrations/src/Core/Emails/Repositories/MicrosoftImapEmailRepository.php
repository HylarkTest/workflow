<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Webklex\PHPIMAP\ClientManager;
use Symfony\Component\Mailer\Transport\Dsn;
use AccountIntegrations\Models\IntegrationAccount;

/**
 * This class cannot be used because the IMAP and SMTP scope use a different
 * Microsoft resource to the other integrations, and so the access token won't
 * work for everything.
 * In the event that it is possible to use IMAP with Microsoft accounts, this
 * class would be useful, so let's keep it around.
 * For more information check out the `MicrosoftRedirectController`.
 */
class MicrosoftImapEmailRepository extends ImapRepository
{
    public function __construct(IntegrationAccount $account)
    {
        $account->refreshToken();

        $clientManager = new ClientManager([]);

        $client = $clientManager->make([
            'host' => 'outlook.office.com',
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

    protected function createDsn(): Dsn
    {
        return new Dsn(
            'smtp',
            'smtp-mail.outlook.com',
            $this->account->account_name,
            $this->account->token,
            587,
        );
    }

    protected function getDraftMailboxId(): string
    {
        return 'Drafts';
    }

    protected function getSentMailboxId(): string
    {
        return 'Sent Items';
    }
}
