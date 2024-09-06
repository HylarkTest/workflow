<?php

declare(strict_types=1);

namespace AccountIntegrations\Core;

use SocialiteProviders\Manager\Exception\MissingConfigException;
use SocialiteProviders\Manager\Helpers\ConfigRetriever as BaseConfigRetriever;

class ConfigRetriever extends BaseConfigRetriever
{
    /**
     * @param  string  $providerName
     * @return array
     *
     * @throws \SocialiteProviders\Manager\Exception\MissingConfigException
     */
    protected function getConfigFromServicesArray($providerName)
    {
        $configArray = config("account-integrations.{$providerName}");

        if (empty($configArray)) {
            $configArray = config("services.{$providerName}");
        }

        if (empty($configArray)) {
            // If we are running in console we should spoof values to make Socialite happy...
            if (app()->runningInConsole()) {
                $configArray = [
                    'client_id' => "{$this->providerIdentifier}_KEY",
                    'client_secret' => "{$this->providerIdentifier}_SECRET",
                    'redirect' => "{$this->providerIdentifier}_REDIRECT_URI",
                ];
            } else {
                throw new MissingConfigException("There is no services entry for $providerName");
            }
        }

        return $this->servicesArray = $configArray;
    }
}
