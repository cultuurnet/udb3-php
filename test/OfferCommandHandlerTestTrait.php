<?php

namespace CultuurNet\UDB3;

use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\RepositoryInterface;
use Broadway\CommandHandling\Testing\Scenario;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description as MediaDescription;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Offer\AgeRange;
use CultuurNet\UDB3\Organizer\Organizer;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionObject;
use ValueObjects\Identity\UUID;
use ValueObjects\Person\Age;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

/**
 * Provides a trait to test commands that are applicable for all UDB3 offer types
 * @property Scenario $scenario
 */
trait OfferCommandHandlerTestTrait
{
    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerRepository;

    /**
     * @var ReadRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelRepository;

    /**
     * Get the namespaced classname of the command to create.
     * @param string $className
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
     * @param string $className
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
        $description = new Description('foo');
        $commandClass = $this->getCommandClass('UpdateDescription');
        $eventClass = $this->getEventClass('DescriptionUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, new Language('nl'), $description)
            )
            ->then([new $eventClass($id, $description)]);
    }

    /**
     * @test
     */
    public function it_can_add_an_image_to_an_offer()
    {
        $id = '1';
        $image = new Image(
            UUID::fromNative('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new MediaDescription('Some description.'),
            new CopyrightHolder('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'),
            new Language('en')
        );
        $commandClass = $this->getCommandClass('AddImage');
        $eventClass = $this->getEventClass('ImageAdded');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [$this->factorOfferCreated($id)]
            )
            ->when(
                new $commandClass($id, $image)
            )
            ->then([new $eventClass($id, $image)]);
    }

    /**
     * @test
     */
    public function it_can_remove_an_image_from_an_offer()
    {
        $id = '1';
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/png'),
            new MediaDescription('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png'),
            new Language('en')
        );
        $imageAddedEventClass = $this->getEventClass('ImageAdded');
        $commandClass = $this->getCommandClass('RemoveImage');
        $eventClass = $this->getEventClass('ImageRemoved');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                    new $imageAddedEventClass($id, $image),
                ]
            )
            ->when(
                new $commandClass($id, $image)
            )
            ->then([new $eventClass($id, $image)]);
    }

    /**
     * @test
     */
    public function it_can_update_an_image_of_an_offer()
    {
        $itemId = '1';
        $mediaObjectId = new UUID('de305d54-75b4-431b-adb2-eb6b9e546014');
        $description = new StringLiteral('A description.');
        $copyrightHolder = new StringLiteral('Dirk');
        $imageAdded = $this->getEventClass('ImageAdded');
        $commandClass = $this->getCommandClass('UpdateImage');
        $eventClass = $this->getEventClass('ImageUpdated');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    $this->factorOfferCreated($itemId),
                    new $imageAdded(
                        $itemId,
                        $anotherImage = new Image(
                            $mediaObjectId,
                            new MIMEType('image/jpeg'),
                            new MediaDescription('my best selfie'),
                            new CopyrightHolder('Dirk Dirkington'),
                            Url::fromNative('http://foo.bar/media/my_best_selfie.gif'),
                            new Language('en')
                        )
                    ),
                ]
            )
            ->when(
                new $commandClass(
                    $itemId,
                    $mediaObjectId,
                    $description,
                    $copyrightHolder
                )
            )
            ->then([
                new $eventClass(
                $itemId,
                $mediaObjectId,
                $description,
                $copyrightHolder
                ),
            ]);
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
        $organizerUpdatedClass = $this->getEventClass('OrganizerUpdated');

        $this->scenario
            ->withAggregateId($id)
            ->given(
                [
                    $this->factorOfferCreated($id),
                    new $organizerUpdatedClass($id, $organizerId),
                ]
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

        $this->organizerRepository
            ->method('load')
            ->willReturn($this->createMock(Organizer::class));

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
     * @expectedException \Broadway\Repository\AggregateNotFoundException
     */
    public function it_should_not_update_an_offer_with_an_unknown_organizer()
    {
        $offerId = '988691DA-8AED-45F7-9794-0577370EAE75';
        $organizerId = 'DD309AA8-208A-4267-AD46-02A7E8082174';
        $commandClass = $this->getCommandClass('UpdateOrganizer');

        $this->organizerRepository
            ->method('load')
            ->with('DD309AA8-208A-4267-AD46-02A7E8082174')
            ->willThrowException(new AggregateNotFoundException($organizerId));

        $this->scenario
            ->withAggregateId($offerId)
            ->given(
                [$this->factorOfferCreated($offerId)]
            )
            ->when(
                new $commandClass($offerId, $organizerId)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_update_typical_agerange_of_an_offer()
    {
        $id = '1';
        $ageRange = new AgeRange(null, new Age(18));
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
