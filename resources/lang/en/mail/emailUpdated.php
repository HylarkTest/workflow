<?php

declare(strict_types=1);

return [
    'subject' => 'Email change confirmation',
    'title' => 'Email change confirmation',
    'intro1' => 'We received a request to update your email address from ":oldEmail" to ":newEmail".',
    'intro2' => 'If you requested this change, you can safely ignore this email. Everything is good with your account.',
    'content' => [
        'header' => '&nbsp;',
        'body' => 'If you did not request this change, please contact us immediately at **:email** to secure your account.',
    ],
    'contact-button' => 'Contact us',
    'contact-email' => [
        'subject' => 'My email address was updated without my consent',
        'body' => <<<'BODY'
            Dear support team,

            I received an email telling me my email address was being updated but I did not request this change.

            Please help me secure my account.
            BODY,
    ],
];
