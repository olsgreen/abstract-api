<?php

namespace Olsgreen\AbstractApi\Http;

use DateTime;

class AccessToken implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var string
     */
    protected $refresh_token;

    /**
     * @var int
     */
    protected $expires;

    public function __construct(array $data)
    {
        if (!empty($data['access_token'])) {
            $this->access_token = $data['access_token'];
        }

        if (!empty($data['refresh_token'])) {
            $this->refresh_token = $data['refresh_token'];
        }

        if (!empty($data['expires'])) {
            $this->expires = $data['expires'];
        }
    }

    public function hasExpired(): bool
    {
        return $this->getExpires() < new DateTime();
    }

    public function getToken():? string
    {
        return $this->access_token;
    }

    public function getRefreshToken():? string
    {
        return $this->refresh_token;
    }

    public function getExpires(): DateTime
    {
        return DateTime::createFromFormat('U', $this->expires);
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->access_token,
            'expires'      => $this->getExpires()
                ->format(DateTime::ATOM),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }

    public function __toString(): string
    {
        return $this->getToken();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
