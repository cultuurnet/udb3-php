<?php

namespace CultuurNet\UDB3\Label\Commands;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class Create extends AbstractCommand
{
    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Privacy
     */
    private $privacy;

    /**
     * Create constructor.
     * @param UUID $uuid
     * @param StringLiteral $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     */
    public function __construct(
        UUID $uuid,
        StringLiteral $name,
        Visibility $visibility,
        Privacy $privacy
    ) {
        parent::__construct($uuid);

        $this->name = $name;
        $this->visibility = $visibility;
        $this->privacy = $privacy;
    }

    /**
     * @return StringLiteral
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return Privacy
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }
}
