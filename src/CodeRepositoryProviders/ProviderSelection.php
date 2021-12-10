<?php

declare(strict_types=1);

namespace App\CodeRepositoryProviders;

class ProviderSelection implements Provider
{
    /**
     * @var array<string, Provider>
     */
    private array $providersMap = [];

    public function registerProvider(string $name, Provider $provider): void
    {
        $this->providersMap[$name] = $provider;
    }

    public function fetch(FetchCriteria $name): iterable
    {
        if (array_key_exists($name->providerName, $this->providersMap)) {
            return $this->providersMap[$name->providerName]->fetch($name);
        }

        throw new \InvalidArgumentException('Provider not known');
    }
}