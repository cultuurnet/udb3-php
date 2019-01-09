<?php

namespace CultuurNet\UDB3\ValueObject;

class SapiVersion
{
    const V2 = 'v2';
    const V3 = 'v3';

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     */
    public function __construct(string $version)
    {
        $this->guardSapiVersion($version);

        $this->version = $version;
    }

    /**
     * @return string
     */
    public function toNative(): string
    {
        return $this->version;
    }

    /**
     * @param SapiVersion $sapiVersion
     * @return bool
     */
    public function equals(SapiVersion $sapiVersion): bool
    {
        return $this->version === $sapiVersion->toNative();
    }

    /**
     * @return array
     */
    private function allowedValues(): array
    {
        return [
            self::V2,
            self::V3,
        ];
    }

    /**
     * @param string $version
     * @throws \InvalidArgumentException
     */
    private function guardSapiVersion(string $version)
    {
        if (!in_array($version, $this->allowedValues())) {
            throw new \InvalidArgumentException(
                'Sapi version "' . $version . '" is not allowed.'
            );
        }
    }

    /**
     * @param string $sapiVersion
     * @return \CultuurNet\UDB3\ValueObject\SapiVersion
     */
    public static function fromNative(string $sapiVersion): SapiVersion
    {
        return new SapiVersion($sapiVersion);
    }

    /**
     * @return \CultuurNet\UDB3\ValueObject\SapiVersion
     */
    public static function V2(): SapiVersion
    {
        return new SapiVersion(self::V2);
    }

    /**
     * @return \CultuurNet\UDB3\ValueObject\SapiVersion
     */
    public static function V3(): SapiVersion
    {
        return new SapiVersion(self::V3);
    }
}
