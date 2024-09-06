<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Telescope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use AccountIntegrations\Core\Emails\Repositories\SendsSmtpEmails;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register()
    {
        if ($this->app->make('config')->get('telescope.dark_mode')) {
            Telescope::night();
        }

        $this->hideSensitiveRequestDetails();

        Telescope::tag(function (IncomingEntry $entry) {
            if (
                $entry->type === 'request'
                && Str::startsWith($entry->content['uri'], '/graphql')
            ) {
                $tags = [];

                if (isset($entry->content['payload']['operations'])) {
                    $tags[] = 'FileUpload';
                }

                $name = $this->getOperationName($entry);
                if ($name) {
                    $tags[] = $name;
                    $entry->content['uri'] .= "($name)";
                }

                $errors = $entry->content['response']['errors'] ?? false;
                if ($errors) {
                    $tag = $this->getErrorTag($errors);
                    if ($tag) {
                        $tags[] = $tag;
                    }
                    $responseStatus = $this->getResponseStatus($errors);
                    if ($responseStatus) {
                        $entry->content['response_status'] = $responseStatus;
                    }
                }

                return $tags;
            }

            return [];
        });

        Telescope::filter(static function (IncomingEntry $entry) {
            return ! ($entry->type === EntryType::MAIL
                && isset($entry->content['mailable'])
                && str_contains($entry->content['mailable'], class_basename(SendsSmtpEmails::class)));
        });

        Telescope::filterBatch(function (Collection $entries) {
            if ($this->app->isLocal() || config('telescope.force_enabled')) {
                return ! $entries->contains(function (IncomingEntry $entry) {
                    return $entry->isRequest() && in_array($entry->content['ip_address'], config('telescope.ignore_ips', []), true);
                });
            }

            return $entries->contains(function (IncomingEntry $entry) {
                return $entry->isReportableException()
                    || $entry->isFailedRequest()
                    || ($entry->isRequest() && isset($entry->content['response']['errors']))
                    || $entry->isFailedJob()
                    || $entry->isScheduledTask()
                    || $entry->hasMonitoredTag()
                    || $entry->type === EntryType::MAIL;
            });
        });
    }

    protected function getOperationName(IncomingEntry $entry): ?string
    {
        $rx = '/(mutation|query)\s(\w+)/';
        if (isset($entry->content['payload']['operationName'])) {
            $name = $entry->content['payload']['operationName'] ?? null;
        } elseif (isset($entry->content['payload']['operations'])) {
            $operations = json_decode($entry->content['payload']['operations'], true, 512, \JSON_THROW_ON_ERROR);

            preg_match($rx, $operations['query'], $matches);

            $name = $matches[2] ?? null;
        } else {
            preg_match($rx, $entry->content['payload']['query'] ?? '', $matches);

            $name = $matches[2] ?? null;
        }

        if (! $name) {
            $errorPath = $entry->content['response']['errors'][0]['path'] ?? false;
            if ($errorPath) {
                $name = implode('.', $errorPath);
            }
        }

        return $name;
    }

    protected function getErrorTag(array $errors): ?string
    {
        return $errors[0]['extensions']['category'] ?? null;
    }

    protected function getResponseStatus(array $errors): ?int
    {
        $category = $errors[0]['extensions']['category'] ?? false;
        if ($category === 'validation') {
            return 422;
        }
        if ($category === 'missing') {
            return 404;
        }

        $message = $errors[0]['message'] ?? false;

        if ($message === 'Internal server error') {
            return 500;
        }

        if ($message === 'Integrated account credentials are invalid.') {
            return 401;
        }

        if (preg_match('/^Variable "\$\w+"/', $message)) {
            return 400;
        }

        return null;
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->isLocal()) {
            return;
        }

        Telescope::hideRequestParameters([
            '_token',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function (User $user) {
            return $user->isAdmin();
        });
    }
}
