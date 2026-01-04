<?php

namespace djchen\OAuth2\Client\Provider;

use Psr\Http\Message\ResponseInterface;

class FitbitRateLimit
{
    private ?string $retryAfter = null;
    private ?string $limit = null;
    private ?string $remaining = null;
    private ?string $reset = null;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 429) {
            $header = $response->getHeader('Retry-After');
            $this->retryAfter = $header[0] ?? null;
        }
        $limitHeader = $response->getHeader('Fitbit-Rate-Limit-Limit');
        $this->limit = $limitHeader[0] ?? null;

        $remainingHeader = $response->getHeader('Fitbit-Rate-Limit-Remaining');
        $this->remaining = $remainingHeader[0] ?? null;

        $resetHeader = $response->getHeader('Fitbit-Rate-Limit-Reset');
        $this->reset = $resetHeader[0] ?? null;
    }

    /**
     * In the event the request is over the rate limit, Fitbit returns the number
     * of seconds until the rate limit is reset and the request should be retried.
     *
     * @return String Number of seconds until request should be retried.
     */
    public function getRetryAfter(): ?string
    {
        return $this->retryAfter;
    }

    /**
     * @return String The quota number of calls.
     */
    public function getLimit(): ?string
    {
        return $this->limit;
    }

    /**
     * @return String The number of calls remaining before hitting the rate limit.
     */
    public function getRemaining(): ?string
    {
        return $this->remaining;
    }

    /**
     * @return String The number of seconds until the rate limit resets.
     */
    public function getReset(): ?string
    {
        return $this->reset;
    }
}
