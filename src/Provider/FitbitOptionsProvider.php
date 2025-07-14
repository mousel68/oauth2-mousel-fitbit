<?php

declare(strict_types=1);

namespace djchen\OAuth2\Client\Provider;

use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;

/**
 * Custom option provider for Fitbit, handling access token request options.
 */
final class FitbitOptionsProvider extends PostAuthOptionProvider
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {
        // Immutable credentials
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getAccessTokenOptions(string $method, array $params): array
    {
        $options = parent::getAccessTokenOptions($method, $params);

        // Securely encode credentials without exposure
        $options['headers']['Authorization'] = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);

        return $options;
    }
}
