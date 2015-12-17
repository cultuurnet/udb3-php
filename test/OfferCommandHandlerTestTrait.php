<?php

/**
 * @file
 * Contains CultuurNet\UDB3\CommandHandlerTestTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\Media\MediaObject;
use ReflectionObject;

/**
 * Provides a trait to test commands that are applicable for all UDB3 offer types
 */
trait OfferCommandHandlerTestTrait
{

    /**
     * Get the namespaced classname of the command to create.
     * @param type $className
     *   Name of the class
     * @return string
     */
    private function getCommandClass($className)
    {
        $reflection = new ReflectionObject($this);
        return $reflection->getNamespaceName() . '\\Commands\\' . $className;
    }

    /**
     * Get the namespaced classname of the event to create.
     * @param type $className
     *   Name of the class
     * @return string
     */
    private function getEventClass($className)
    {
        $reflection = new ReflectionObject($this);
        return $reflection->getNamespaceName() . '\\Events\\' . $className;
    }

    /**
     * @test
     */
    public function it_can_update_booking_info_of_an_offer()
    {
        $id = '1';
        $bookingInfo = new BookingInfo();
        $commandClass = $this->getCommandClass('UpdateBookingInfo');
        $eventClass = $this->getEventClass('BookingInfoUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $bookingInfo)
            )
            ->then([new $eventClass($id, $bookingInfo)]);
    }

    /**
     * @test
     */
    public function it_can_update_contact_point_of_an_offer()
    {
        $id = '1';
        $contactPoint = new ContactPoint();
        $commandClass = $this->getCommandClass('UpdateContactPoint');
        $eventClass = $this->getEventClass('ContactPointUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $contactPoint)
            )
            ->then([new $eventClass($id, $contactPoint)]);
    }

    /**
     * @test
     */
    public function it_can_update_description_of_an_offer()
    {
        $id = '1';
        $description = 'foo';
        $commandClass = $this->getCommandClass('UpdateDescription');
        $eventClass = $this->getEventClass('DescriptionUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $description)
            )
            ->then([new $eventClass($id, $description)]);
    }

    /**
     * @test
     */
    public function it_can_add_an_image_to_an_offer()
    {
        $id = '1';
        $mediaObject = new MediaObject('$url', '$thumbnailUrl', '$description', '$copyrightHolder');
        $commandClass = $this->getCommandClass('AddImage');
        $eventClass = $this->getEventClass('ImageAdded');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $mediaObject)
            )
            ->then([new $eventClass($id, $mediaObject)]);
    }

    /**
     * @test
     */
    public function it_can_add_delete_an_image_of_an_offer()
    {
        $id = '1';
        $indexToDelete = 1;
        $internalId = '1';
        $commandClass = $this->getCommandClass('DeleteImage');
        $eventClass = $this->getEventClass('ImageDeleted');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $indexToDelete, $internalId)
            )
            ->then([new $eventClass($id, $indexToDelete, $internalId)]);
    }

    /**
     * @test
     */
    public function it_can_add_update_an_image_of_an_offer()
    {
        $id = '1';
        $index = 1;
        $mediaObject = new MediaObject('$url', '$thumbnailUrl', '$description', '$copyrightHolder');
        $commandClass = $this->getCommandClass('UpdateImage');
        $eventClass = $this->getEventClass('ImageUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $index, $mediaObject)
            )
            ->then([new $eventClass($id, $index, $mediaObject)]);
    }

    /**
     * @test
     */
    public function it_can_delete_an_organizer_of_an_offer()
    {
        $id = '1';
        $organizerId = '5';
        $commandClass = $this->getCommandClass('DeleteOrganizer');
        $eventClass = $this->getEventClass('OrganizerDeleted');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $organizerId)
            )
            ->then([new $eventClass($id, $organizerId)]);
    }

    /**
     * @test
     */
    public function it_can_update_organizer_of_an_offer()
    {
        $id = '1';
        $organizer = '1';
        $commandClass = $this->getCommandClass('UpdateOrganizer');
        $eventClass = $this->getEventClass('OrganizerUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $organizer)
            )
            ->then([new $eventClass($id, $organizer)]);
    }

    /**
     * @test
     */
    public function it_can_update_typical_agerange_of_an_offer()
    {
        $id = '1';
        $ageRange = '-18';
        $commandClass = $this->getCommandClass('UpdateTypicalAgeRange');
        $eventClass = $this->getEventClass('TypicalAgeRangeUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $ageRange)
            )
            ->then([new $eventClass($id, $ageRange)]);
    }

    /**
     * @test
     */
    public function it_can_delete_typical_agerange_of_an_offer()
    {
        $id = '1';
        $commandClass = $this->getCommandClass('DeleteTypicalAgeRange');
        $eventClass = $this->getEventClass('TypicalAgeRangeDeleted');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id)
            )
            ->then([new $eventClass($id)]);
    }
}
