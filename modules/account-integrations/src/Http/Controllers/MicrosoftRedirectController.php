<?php

declare(strict_types=1);

namespace AccountIntegrations\Http\Controllers;

use AccountIntegrations\Core\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;

class MicrosoftRedirectController extends RedirectController
{
    public const SCOPES = [
        'CALENDAR' => ['Calendars.ReadWrite.Shared', 'Calendars.ReadWrite'],
        'TODOS' => ['Tasks.ReadWrite.Shared', 'Tasks.ReadWrite'],
        'DOCUMENTS' => ['Files.ReadWrite'],
        'EMAILS' => ['Mail.ReadWrite', 'Mail.Send'],
        /*
         * Be careful here. Don't fall into the same trap I did.
         * It is possible to use IMAP with OAuth through Azure, however it
         * needs to be with a different access token to the other Microsoft
         * graph endpoints. Check out the description of the `scope` property
         * in the token request:
         * https://docs.microsoft.com/en-us/graph/auth-v2-user#token-request
         * "If the scopes specified in this request span multiple resource
         * servers, then the v2.0 endpoint will return a token for the resource
         * specified in the first scope."
         * This means that the access token only worked for IMAP when the IMAP
         * scope was first, but it didn't work for any other resource. And vice
         * versa. This was not clear and very annoying to find out.
         * It seems that if we are to use IMAP for Microsoft accounts in the
         * future we need to ask the user to sign in twice, once for the IMAP
         * token, and again for all the other integrations.
         * > Also be aware that Socialite will automatically add the `User.Read`
         * scope to the OAuth request, so Socialite cannot be used to fetch the
         * IMAP token.
         * This is not ideal, so until a solution is found we will use the
         * Microsoft Graph API for emails, even though it involves maintaining
         * two email gateways.
         */
        // 'IMAP_EMAILS' => ['https://outlook.office.com/IMAP.AccessAsUser.All', 'https://outlook.office.com/SMTP.Send'],
        'CONTACTS' => ['Contacts.ReadWrite.Shared'],
    ];

    protected function getOauthProvider(): ProviderInterface
    {
        /** @var \SocialiteProviders\Azure\Provider $provider */
        $provider = Socialite::driver('azure');

        return $provider->scopes(['offline_access']);
    }

    protected function getProvider(): Provider
    {
        return Provider::MICROSOFT;
    }

    protected function getScopeMap(): array
    {
        return self::SCOPES;
    }

    protected static function getRedirectName(): string
    {
        return 'integrations.microsoft.redirect';
    }
}
