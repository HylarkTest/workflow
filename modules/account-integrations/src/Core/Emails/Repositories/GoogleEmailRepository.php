<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails\Repositories;

use Google\Client;
use Google\Service\Gmail;
use Illuminate\Support\Collection;
use AccountIntegrations\Core\Emails\Email;
use AccountIntegrations\Models\IntegrationAccount;

class GoogleEmailRepository extends GoogleImapEmailRepository
{
    protected Client $client;

    protected Gmail $gmailApi;

    public function __construct(IntegrationAccount $account)
    {
        $this->client = $account->getGoogleClient();
        $this->gmailApi = new Gmail($this->client);
        parent::__construct($account);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \AccountIntegrations\Core\Emails\Email>
     *
     * @throws \Webklex\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Webklex\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Webklex\PHPIMAP\Exceptions\ResponseException
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    public function getEmailsSummary(?string $mailboxId = null, array $options = []): Collection
    {
        if (! $mailboxId) {
            $mailboxId = '[Gmail]/All Mail';
        }

        return parent::getEmailsSummary($mailboxId, $options);
    }

    public function getEmail(string $emailId, ?string $mailboxId = null): Email
    {
        if (! $mailboxId) {
            $mailboxId = '[Gmail]/All Mail';
        }

        return parent::getEmail($emailId, $mailboxId);
    }
}
