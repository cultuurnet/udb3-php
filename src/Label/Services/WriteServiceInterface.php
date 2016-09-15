<?php

namespace CultuurNet\UDB3\Label\Services;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use ValueObjects\Identity\UUID;

interface WriteServiceInterface
{
    /**
     * @param LabelName $name
     * @param Visibility $visibility
     * @param Privacy $privacy
     * @return WriteResult
     */
    public function create(
        LabelName $name,
        Visibility $visibility,
        Privacy $privacy
    );

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makeVisible(UUID $uuid);

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makeInvisible(UUID $uuid);

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makePublic(UUID $uuid);

    /**
     * @param UUID $uuid
     * @return WriteResult
     */
    public function makePrivate(UUID $uuid);
}
