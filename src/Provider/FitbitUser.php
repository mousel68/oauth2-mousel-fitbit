<?php

namespace djchen\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class FitbitUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $userInfo = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->userInfo = $response['user'] ?? [];
    }

    public function getId(): ?string
    {
        return $this->userInfo['encodedId'] ?? null;
    }

    /**
     * Get the display name.
     *
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->userInfo['displayName'] ?? null;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->userInfo;
    }
}
