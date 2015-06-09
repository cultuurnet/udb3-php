<?php

/**
 * @file
 * Contains CultuurNet\UDB3\OfferLDProjectorTestTrait.
 */

namespace CultuurNet\UDB3;

use CultuurNet\UDB3\ReadModel\JsonDocument;

/**
 * Provides a trait to test ld projection that is applicable for all UDB3 offer types
 */
trait OfferLDProjectorTestTrait
{

    /**
     * Get the namespaced classname of the event to create.
     * @param type $className
     *   Name of the class
     * @return string
     */
    private function getEventClass($className)
    {
        $reflection = new \ReflectionObject($this);
        return $reflection->getNamespaceName() . '\\Events\\' . $className;
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_booking_info()
    {

        $id = 'foo';
        $url = 'http://www.google.be';
        $urlLabel = 'Google';
        $phone = '045';
        $email = 'test@test.com';
        $availabilityStarts = '12';
        $availabilityEnds = '14';
        $name = 'Booking name';
        $description = 'booking description';
        $bookingInfo = new BookingInfo($url, $urlLabel, $phone, $email, $availabilityStarts, $availabilityEnds, $name, $description);
        $eventClass = $this->getEventClass('BookingInfoUpdated');
        $bookingInfoUpdated = new $eventClass($id, $bookingInfo);

        $initialDocument = new JsonDocument($id);

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'bookingInfo' => [
                    'phone' => $phone,
                    'email' => $email,
                    'url' => $url,
                    'urlLabel' => $urlLabel,
                    'name' => $name,
                    'description' => $description,
                    'availabilityStarts' => $availabilityStarts,
                    'availabilityEnds' => $availabilityEnds
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyBookingInfoUpdated($bookingInfoUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_contact_point()
    {

        $id = 'foo';
        $phones = ['045', '046'];
        $emails = ['test@test.be', 'test@test2.be'];
        $urls = ['http://www.google.be', 'http://www.google2.be'];
        $type = 'type';
        $contactPoint = new ContactPoint($phones, $emails, $urls, $type);
        $eventClass = $this->getEventClass('ContactPointUpdated');
        $contactPointUpdated = new $eventClass($id, $contactPoint);

        $initialDocument = new JsonDocument($id);

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'contactPoint' => [
                    'phone' => $phones,
                    'email' => $emails,
                    'url' => $urls,
                    'type' => $type,
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyContactPointUpdated($contactPointUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_description()
    {
        $description = 'description';
        $id = 'foo';
        $eventClass = $this->getEventClass('DescriptionUpdated');
        $descriptionUpdated = new $eventClass($id, $description);

        $initialDocument = new JsonDocument($id);

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'description' => [
                    'nl' => $description
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyDescriptionUpdated($descriptionUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_adding_of_an_image()
    {
        $id = 'foo';
        $url = '$url';
        $thumbnailUrl = '$thumbnailUrl';
        $description = '$description';
        $copyrightHolder = '$copyrightHolder';
        $type = '$type';

        $mediaObject = new MediaObject($url, $thumbnailUrl, $description, $copyrightHolder, '', $type);
        $eventClass = $this->getEventClass('ImageAdded');
        $imageAdded = new $eventClass($id, $mediaObject);

        $initialDocument = new JsonDocument($id);

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [[
                    '@type' => $type,
                    'url' => $url,
                    'thumbnailUrl' => $thumbnailUrl,
                    'description' => $description,
                    'copyrightHolder' => $copyrightHolder
                ]]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyImageAdded($imageAdded);
    }

    /**
     * @test
     */
    public function it_projects_the_editing_of_an_image()
    {
        $id = 'foo';
        $url = '$url';
        $internalId = '$internalId';
        $thumbnailUrl = '$thumbnailUrl';
        $description = '$description';
        $copyrightHolder = '$copyrightHolder';
        $type = '$type';

        $mediaObject = new MediaObject($url, $thumbnailUrl, $description, $copyrightHolder, $internalId, $type);
        $eventClass = $this->getEventClass('ImageUpdated');
        $imageUpdated = new $eventClass($id, 0, $mediaObject);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [[
                    '@type' => 'oldtype',
                    'url' => 'oldUrl',
                    'thumbnailUrl' => 'oldthumbnailUrl',
                    'description' => 'olddescription',
                    'copyrightHolder' => 'oldcopyrightHolder'
                ]]
            ])
        );

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [[
                    '@type' => $type,
                    'url' => $url,
                    'thumbnailUrl' => $thumbnailUrl,
                    'description' => $description,
                    'copyrightHolder' => $copyrightHolder
                ]]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyImageUpdated($imageUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_deleting_of_an_image()
    {
        $id = 'foo';
        $eventClass = $this->getEventClass('ImageDeleted');
        $imageDeleted = new $eventClass($id, 1, 'internalId');

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [
                    [
                      '@type' => 'oldtype',
                      'url' => 'oldUrl',
                      'thumbnailUrl' => 'oldthumbnailUrl',
                      'description' => 'olddescription',
                      'copyrightHolder' => 'oldcopyrightHolder'
                    ],
                    [
                      '@type' => 'deleteType',
                      'url' => 'deleteUrl',
                      'thumbnailUrl' => 'deleteThumbnail',
                      'description' => 'deleteDescription',
                      'copyrightHolder' => 'deleteCopyrightHolder'
                    ],
                    [
                      '@type' => 'lasttype',
                      'url' => 'lestUrl',
                      'thumbnailUrl' => 'lastThumbnailUrl',
                      'description' => 'lastDescription',
                      'copyrightHolder' => 'lastCopyrightHolder'
                    ]
                ]
            ])
        );

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'mediaObject' => [
                    [
                      '@type' => 'oldtype',
                      'url' => 'oldUrl',
                      'thumbnailUrl' => 'oldthumbnailUrl',
                      'description' => 'olddescription',
                      'copyrightHolder' => 'oldcopyrightHolder'
                    ],
                    [
                      '@type' => 'lasttype',
                      'url' => 'lestUrl',
                      'thumbnailUrl' => 'lastThumbnailUrl',
                      'description' => 'lastDescription',
                      'copyrightHolder' => 'lastCopyrightHolder'
                    ]
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyImageDeleted($imageDeleted);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_the_organizer()
    {

        $id = 'foo';
        $organizerId = 'ORGANIZER-ABC-456';

        $this->organizerService->expects($this->once())
            ->method('getEntity')
            ->with($organizerId)
            ->willThrowException(new EntityNotFoundException());
        $this->organizerService->expects($this->once())
            ->method('iri')
            ->willReturnCallback(
                function ($argument) {
                    return 'http://example.com/entity/' . $argument;
                }
            );

        $eventClass = $this->getEventClass('OrganizerUpdated');
        $organizerUpdated = new $eventClass($id, $organizerId);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'organizer' => [
                    '@type' => 'Organizer',
                    '@id' => 'http://example.com/entity/ORGANIZER-ABC-123'
                ]
            ])
        );

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'organizer' => [
                    '@type' => 'Organizer',
                    '@id' => 'http://example.com/entity/' . $organizerId
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyOrganizerUpdated($organizerUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_an_existing_organizer()
    {

        $id = 'foo';
        $organizerId = 'ORGANIZER-ABC-456';

        $this->organizerService->expects($this->once())
            ->method('getEntity')
            ->with($organizerId)
            ->willReturnCallback(
                function ($argument) {
                    return json_encode(['id' => $argument, 'name' => 'name']);
                }
            );

        $eventClass = $this->getEventClass('OrganizerUpdated');
        $organizerUpdated = new $eventClass($id, $organizerId);

        $initialDocument = new JsonDocument($id);

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'organizer' => [
                    '@type' => 'Organizer',
                    'id' => $organizerId,
                    'name' => 'name',
                ]
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyOrganizerUpdated($organizerUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_deleting_of_the_organizer()
    {

        $id = 'foo';
        $organizerId = 'ORGANIZER-ABC-123';

        $eventClass = $this->getEventClass('OrganizerDeleted');
        $organizerDeleted = new $eventClass($id, $organizerId);

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'organizer' => [
                    '@type' => 'Organizer',
                    '@id' => 'http://example.com/entity/' . $organizerId
                ]
            ])
        );

        $expectedDocument = new JsonDocument($id);

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyOrganizerDeleted($organizerDeleted);
    }

    /**
     * @test
     */
    public function it_projects_the_updating_of_age_range()
    {

        $id = 'foo';
        $eventClass = $this->getEventClass('TypicalAgeRangeUpdated');
        $typicalAgeRangeUpdated = new $eventClass($id, '-18');

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'typicalAgeRange' => '12-14'
            ])
        );

        $expectedDocument = new JsonDocument(
            $id,
            json_encode([
                'typicalAgeRange' => '-18'
            ])
        );

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyTypicalAgeRangeUpdated($typicalAgeRangeUpdated);
    }

    /**
     * @test
     */
    public function it_projects_the_deleting_of_age_range()
    {
        $id = 'foo';
        $eventClass = $this->getEventClass('TypicalAgeRangeUpdated');
        $typicalAgeRangeUpdated = new $eventClass($id, '-1');

        $initialDocument = new JsonDocument(
            $id,
            json_encode([
                'typicalAgeRange' => '-18'
            ])
        );

        $expectedDocument = new JsonDocument($id);

        $this->documentRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($initialDocument);

        $this->documentRepository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($expectedDocument) {
                    return $expectedDocument == $jsonDocument;
                }
            ));

        $this->projector->applyTypicalAgeRangeUpdated($typicalAgeRangeUpdated);
    }
}
