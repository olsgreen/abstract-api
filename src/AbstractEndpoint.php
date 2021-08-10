<?php

namespace Olsgreen\AbstractApi;

use Olsgreen\AbstractApi\Http\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractEndpoint
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function booleanNormalizer()
    {
        return function ($options, $value) {
            return $value ? 'true' : 'false';
        };
    }

    protected function isEmptyResponse(ResponseInterface $response): bool
    {
        return empty((string) $response->getBody());
    }

    protected function isJsonResponse(ResponseInterface $response): bool
    {
        if ($response->hasHeader('Content-Type')) {
            $types = array_map(function ($type) {
                $clean = explode(';', $type);

                return $clean[0];
            }, $response->getHeader('Content-Type'));

            return in_array('application/json', $types);
        }

        return false;
    }

    protected function parseResponse(ResponseInterface $response, bool $isDownload = false): array
    {
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
            if ($this->isJsonResponse($response)) {
                $decoded = json_decode((string) $response->getBody(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception(
                        sprintf(
                            'There was a problem decoding the response body: %s',
                            json_last_error_msg()
                        )
                    );
                }

                return $decoded;
            } elseif ($isDownload || $this->isEmptyResponse($response)) {
                return [];
            }

            $truncatedBody = substr((string) $response->getBody(), 0, 100);

            throw new \Exception('The response had an unsupported Content-Type: ' . $truncatedBody);
        }
    }

    protected function handleHttpException(HttpException $ex)
    {
        throw $ex;
    }

    protected function _get(string $uri, array $params = [], array $headers = [], $sink = null): array
    {
        try {
            $response = $this->client->getHttp()->get($uri, $params, $headers, $sink);
        } catch (HttpException $ex) {
            $this->handleHttpException($ex);
        }

        return $this->parseResponse($response, isset($sink));
    }

    protected function _post(string $uri, array $params = [], $body = null, array $headers = []): array
    {
        try {
            $response = $this->client->getHttp()->post($uri, $params, $body, $headers);
        } catch (HttpException $ex) {
            $this->handleHttpException($ex);
        }

        return $this->parseResponse($response);
    }

    protected function _put(string $uri, array $params = [], $body = null, array $headers = []): array
    {
        try {
            $response = $this->client->getHttp()->put($uri, $params, $body, $headers);
        } catch (HttpException $ex) {
            $this->handleHttpException($ex);
        }

        return $this->parseResponse($response);
    }

    protected function _patch(string $uri, array $params = [], $body = null, array $headers = []): array
    {
        try {
            $response = $this->client->getHttp()->patch($uri, $params, $body, $headers);
        } catch (HttpException $ex) {
            $this->handleHttpException($ex);
        }

        return $this->parseResponse($response);
    }

    protected function _delete(string $uri, array $params = [], array $headers = []): array
    {
        try {
            $response = $this->client->getHttp()->delete($uri, $params, $headers);
        } catch (HttpException $ex) {
            $this->handleHttpException($ex);
        }

        return $this->parseResponse($response);
    }
}
