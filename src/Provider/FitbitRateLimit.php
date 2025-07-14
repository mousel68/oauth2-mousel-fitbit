<?php

declare(strict_types=1);

namespace djchen\OAuth2\Client\Provider;

use Psr\Http\Message\ResponseInterface;

/**
 * Handles Fitbit API rate limit information from responses.
 */
final class FitbitRateLimit
{
    public function __construct(
        private readonly ?string $retryAfter,
        private readonly ?string $limit,
        private readonly ?string $remaining,
        private readonly ?string $reset
    ) {
        // Constructor promotion for immutability
    }

    /**
     * Factory method to create from response, with safe header extraction.
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        $retryAfter = null;
        if ($response->getStatusCode() === 429) {
            $retryHeaders = $response->getHeader('Retry-After');
            $retryAfter = $retryHeaders[0] ?? null;
        }

        $limitHeaders = $response->getHeader('Fitbit-Rate-Limit-Limit');
        $remainingHeaders = $response->getHeader('Fitbit-Rate-Limit-Remaining');
        $resetHeaders = $response->getHeader('Fitbit-Rate-Limit-Reset');

        return new self(
            retryAfter: $retryAfter,
            limit: $limitHeaders[0] ?? null,
            remaining: $remainingHeaders[0] ?? null,
            reset: $resetHeaders[0] ?? null
        );
    }

    /**
     * In the event the request is over the rate limit, Fitbit returns the number
     * of seconds until the rate limit is reset and the request should be retried.
     */
    public function getRetryAfter(): ?string
    {
        return $this->retryAfter;
    }

    public function getLimit(): ?string
    {
        return $this->limit;
    }

    public function getRemaining(): ?string
    {
        return $this->remaining;
    }

    public function getReset(): ?string
    {
        return $this->reset;
    }
}
