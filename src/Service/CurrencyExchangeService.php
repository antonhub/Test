<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TODO implement a parent class and child classes for each neded currency
 */
class CurrencyExchangeService implements CurrencyExchangeServiceInterface
{
    private ?bool $isServiceAvailable = null;
    private array $euroExchangeRatesArr = [];

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly HttpClientInterface   $httpClient,
    ) {
    }

    /**
     * Getting the service status since it depends on the availability of the online service we're calling
     *
     * @return bool
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    final public function isServiceAvailable(): bool
    {
        if ($this->isServiceAvailable === null) {
            $this->init();
        }

        return $this->isServiceAvailable;
    }

    /**
     * Getting the euro currency exchange rate for the given currency alpha code
     * Reference to ISO_4217 https://en.wikipedia.org/wiki/ISO_4217
     * TODO implement the same function for the currency 'numericCode'
     *
     * @param string $currencyAlphaCode
     * @return float|null
     */
    final public function getEuroInverseExchangeRateByAlphaCode(string $currencyAlphaCode): ?float
    {
        // not valid currency alpha code
        if (strlen($currencyAlphaCode) !== 3) {
            return null;
        }

        $currencyAlphaCode = strtolower($currencyAlphaCode);

        $euroExchangeRates = $this->getEuroExchangeRates();

        if (!isset($euroExchangeRates[$currencyAlphaCode])) {
            return null;
        }

        $exchangeRateArr = $euroExchangeRates[$currencyAlphaCode];

        if (!isset($exchangeRateArr['inverseRate'])) {
            return null;
        }

        // TODO the same function for 'rate'
        return (float) $exchangeRateArr['inverseRate'];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getEuroExchangeRates(): ?array
    {
        if ($this->isServiceAvailable === null) {
            // TODO replace it with cache
            $this->init();
        }

        return $this->euroExchangeRatesArr;
    }

    /**
     * Getting today's currency exchange rates from the internet
     *
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function init(): void
    {
        // the service is already initialized
        if ($this->isServiceAvailable !== null) {
            return;
        }

        $this->isServiceAvailable = false;

        // currency exchange feeder url from the parameters config
        $euroExchangeRatesUrl = $this->parameterBag->get('euro_exchange_feed_url');

        if (
            !is_string($euroExchangeRatesUrl)
            || empty($euroExchangeRatesUrl)
        ) {
            return;
        }

        // sending a request
        $response = $this->httpClient->request(
            'GET',
            $euroExchangeRatesUrl
        );

        $responseContent = $response->getContent();

        // something went wrong
        if (
            $response->getStatusCode() !== 200
            || !json_validate($responseContent)
        ) {
            return;
        }

        $ratesContentArr = json_decode($responseContent, true);

        if (
            empty($ratesContentArr)
        ) {
            return;
        }

        $this->isServiceAvailable = true;

        $this->euroExchangeRatesArr = $ratesContentArr;
    }
}
