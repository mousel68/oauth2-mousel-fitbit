<?php

declare(strict_types=1);

namespace djchen\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * OAuth2 provider for Fitbit API.
 */
final class Fitbit extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private const BASE_FITBIT_URL = 'https://www.fitbit.com';
    private const BASE_FITBIT_API_URL = 'https://api.fitbit.com';
    private const HEADER_ACCEPT_LANG = 'Accept-Language';
    private const HEADER_ACCEPT_LOCALE = 'Accept-Locale';

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (!isset($options['clientId'], $options['clientSecret'])) {
            throw new \InvalidArgumentException('Missing required options: clientId and clientSecret');
        }

        $collaborators['optionProvider'] = new FitbitOptionsProvider(
            $options['clientId'],
            $options['clientSecret']
        );

        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl(): string
    {
        return self::BASE_FITBIT_URL . '/oauth2/authorize';
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::BASE_FITBIT_API_URL . '/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return self::BASE_FITBIT_API_URL . '/1/user/-/profile.json';
    }

    /**
     * @return array<string>
     */
    protected function getDefaultScopes(): array
    {
        return ['activity', 'heartrate', 'location', 'profile', 'settings', 'sleep', 'social', 'weight', 'nutrition'];
    }

    /**
     * @param ResponseInterface $response
     * @param mixed $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {  // Note: Original had >=400, kept for consistency
            $errorMessage = '';
            if (is_array($data) || empty($data['errors'])) {
                $errorMessage = $response->getReasonPhrase();
            } else {
                foreach ($data['errors'] as $error) {
                    if (!empty($errorMessage)) {
                        $errorMessage .= ' , ';
                    }
                    $errorMessage .= implode(' - ', (array)$error);
                }
            }
            throw new IdentityProviderException(
                $errorMessage,
                $response->getStatusCode(),
                $data
            );
        }
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function getAuthorizationParameters(array $options): array
    {
        $params = parent::getAuthorizationParameters($options);
        unset($params['approval_prompt']);
        if (!empty($options['prompt'])) { {
            $params['prompt'] = $options['prompt'];
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function createResourceOwner(array $response, AccessToken $token): FitbitUser
    {
        return new FitbitUser($response);
    }

    protected function getAccessTokenResourceOwnerId(): ?string
    {
        return 'user_id';
    }

    public function revoke(AccessToken $accessToken): ResponseInterface
    {
        $options = $this->optionProvider->getAccessTokenOptions(self::METHOD_POST, []);

        $uri = $this->appendQuery(
            self::BASE_FITBIT_API_URL . '/oauth2/revoke',
            $this->buildQueryString(['token' => $accessToken->getToken()])
        );

        $request = $this->getRequest(self::METHOD_POST, $uri, $options);

        return $this->getResponse($request);
    }

    public function parseResponse(ResponseInterface $response): mixed
    {
        return parent::parseResponse($response);
    }

    public function getFitbitRateLimit(ResponseInterface $response): FitbitRateLimit
    {
        return FitbitRateLimit::fromResponse($response);
    }
}
