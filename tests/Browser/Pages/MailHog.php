<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class MailHog extends Page
{
    public function url(): string
    {
        return 'http://'.config('mail.mailers.smtp.host').':8025';
    }

    public function elements(): array
    {
        return [
            '@messages' => '.message',
            '@firstEmail' => '.message:first-of-type',
            '@lastEmail' => '.message:last-of-type',
            '@primaryButton' => '.button-primary',
            '@preview' => '#preview-html',
        ];
    }

    public function openFirstEmail(Browser $browser, ?string $subject = null): void
    {
        if ($subject === null) {
            $browser->press('@firstEmail');
        }
        $resolver = $browser->resolver;
        $emails = $resolver->all('.msglist-message .subject');
        foreach ($emails as $element) {
            if (Str::contains($element->getText(), $subject)) {
                $element->click();

                return;
            }
        }
    }

    public function openLastEmail(Browser $browser, ?string $subject = null): void
    {
        if ($subject === null) {
            $browser->press('@lastEmail');
        }
        $resolver = $browser->resolver;
        $emails = array_reverse($resolver->all('.subject'));
        foreach ($emails as $element) {
            if (Str::contains($element->getText(), $subject)) {
                $element->click();

                return;
            }
        }
    }

    public function waitForPreview(Browser $browser): void
    {
        $browser->waitFor('@preview');
    }

    public function inPreview(Browser $browser, callable $callback): void
    {
        $browser->waitForPreview()
            ->withinFrame('@preview', $callback);
    }

    public function clickOnCallToAction(Browser $browser): void
    {
        $browser->inPreview(function (Browser $preview) use ($browser) {
            $preview->waitFor('@primaryButton');
            $button = $preview->resolver->find('@primaryButton');
            $url = $button->getAttribute('href');
            $browser->visit($url);
        });
    }

    public function clearEmails(): void
    {
        Http::delete($this->url().'/api/v1/messages');
    }
}
