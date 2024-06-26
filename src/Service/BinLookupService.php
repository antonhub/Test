<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BinLookupService implements BinLookupServiceInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly HttpClientInterface   $httpClient,
        private readonly CacheService          $cacheService,
    ) {}

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws InvalidArgumentException
     */
    final public function getCountryAlpha2CodeByBin(string $bin): ?string
    {
        // getting BIN lookup API Response
        $response = $this->getBinLookupHttpResponse($bin);

        // validate the response
        if ( ! $this->isBinLookupResponseValid($response) ) {
            return null;
        }

        $contentArr = json_decode($response->getContent(), true);

        // TODO consider to take into account ['country']['currency'] and ['country']['numeric'] keys
        if (
            ! is_array($contentArr)
            || empty($contentArr)
            || empty($contentArr['country']['alpha2'])
            || !is_string($contentArr['country']['alpha2'])
            || strlen($contentArr['country']['alpha2']) !== 2
        ) {
            return null;
        }

        return $contentArr['country']['alpha2'];
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function isBinLookupResponseValid(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 200
            && str_starts_with($response->getHeaders()['content-type'][0], 'application/json')
            && json_validate($response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    private function getBinLookupHttpResponse(string $bin): ResponseInterface
    {
        $binLookupUrl = $this->prepareBinLookupUrl($bin);

        // check the cached value
        $cachedResponse = $this->cacheService->get($binLookupUrl);

        if ( $cachedResponse instanceof ResponseInterface) {
            return $cachedResponse;
        }

        // sending a request
        $response = $this->httpClient->request(
            'GET',
            $binLookupUrl
        );

        // caching the response
        $this->cacheService->set($binLookupUrl, $response);

        return $response;
    }

    private function prepareBinLookupUrl(string $bin): ?string
    {
        if (empty($bin)) {
            return null;
        }

        // the BIN lookup url from the parameters config
        $binLookupUrl = $this->parameterBag->get('bin_lookup_url');

        if (
            $binLookupUrl === null
            || !is_string($binLookupUrl)
            || empty($binLookupUrl)
        ) {
            return null;
        }

        // trim URL just in case
        $binLookupUrl = trim(trim($binLookupUrl), '/');

        // adding the BIN to URL
        return $binLookupUrl . '/' . $bin;
    }
}