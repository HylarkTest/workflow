<?php

declare(strict_types=1);

return [
    'header' => 'There was a new sign-in to your Hylark from an unknown device.',
    'preview' => 'If this was you, you don\'t need to do anything.',
    'content' => <<<'HTML'
If this was you, you don't need to do anything.
<br>
If not, please review your security settings and change your password.
<ul>
    <li>Browser: <strong>:browser</strong></li>
    <li>Device: <strong>:device</strong></li>
    <li>IP address: <strong>:ip</strong></li>
</ul>
HTML,
];
