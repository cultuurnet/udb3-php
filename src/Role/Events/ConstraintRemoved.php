<?php

namespace CultuurNet\UDB3\Role\Events;

use CultuurNet\UDB3\ValueObject\SapiVersion;
use ValueObjects\Identity\UUID;

final class ConstraintRemoved extends AbstractEvent
{
    /**
     * @var SapiVersion
     */
    private $sapiVersion;

    /**
     * @param UUID $uuid
     * @param SapiVersion $sapiVersion
     */
    final public function __construct(
        UUID $uuid,
        SapiVersion $sapiVersion
    ) {
        parent::__construct($uuid);
        $this->sapiVersion = $sapiVersion;
    }

    /**
     * @return SapiVersion
     */
    public function getSapiVersion(): SapiVersion
    {
        return $this->sapiVersion;
    }

    /**
     * @inheritdoc
     */
    public static function deserialize(array $data): ConstraintRemoved
    {
        return new static(
            new UUID($data['uuid']),
            SapiVersion::fromNative($data['sapiVersion'])
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize(): array
    {
        return parent::serialize() + array(
                'sapiVersion' => $this->sapiVersion->toNative(),
            );
    }
}
