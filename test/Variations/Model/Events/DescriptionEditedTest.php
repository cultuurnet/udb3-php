<?php

namespace CultuurNet\UDB3\Variations\Model\Events;

use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;

class DescriptionEditedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function is_serializable()
    {
        $descriptionEditedEvent = new DescriptionEdited(
            new Id('29d6d973-ca78-4561-b593-631502c74a8c'),
            new Description('This is a short personalized description for an event')
        );

        $serializedEvent = $descriptionEditedEvent->serialize();
        $deserializedEvent = DescriptionEdited::deserialize($serializedEvent);

        $this->assertEquals($deserializedEvent, $descriptionEditedEvent);
    }
}
