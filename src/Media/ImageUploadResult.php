<?php

namespace CultuurNet\UDB3\Media;

use ValueObjects\Identity\UUID;

class ImageUploadResult
{
    /**
     * @var UUID
     */
    private $imageId;

    /**
     * @var string
     */
    private $jobId;

    /**
     * @param UUID $imageId
     * @param string $jobId
     */
    public function __construct(
        UUID $imageId,
        $jobId
    ) {
        $this->imageId = $imageId;
        $this->jobId = $jobId;
    }

    /**
     * @return UUID
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }
}
