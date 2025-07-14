<?php

declare(strict_types=1);

namespace djchen\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Represents a Fitbit user resource owner.
 */
final class FitbitUser implements ResourceOwnerInterface
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        private readonly array $userInfo
    ) {
        // Extract user info safely, ensuring it's an array
        $this->userInfo = $response['user'] ?? throw new \InvalidArgumentException('Invalid response: missing "user" key');
    }

    public function getId(): string
    {
        return $this->userInfo['encodedId'] ?? throw new \RuntimeException('User ID not found');
    }

    /**
     * Get the display name.
     */
    public function getDisplayName(): string
    {
        return $this->userInfo['displayName'] ?? '';
    }

    /**
     * Get user data as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->userInfo;
    }
}
