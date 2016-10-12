<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Address;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Events\AddressUpdated;
use CultuurNet\UDB3\Organizer\Events\ContactPointUpdated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class OrganizerTest extends AggregateRootScenarioTestCase
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Url
     */
    private $website;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var OrganizerCreatedWithUniqueWebsite
     */
    private $organizerCreatedWithUniqueWebsite;

    public function setUp()
    {
        parent::setUp();

        $this->id = '18eab5bf-09bf-4521-a8b4-c0f4a585c096';
        $this->website = Url::fromNative('http://www.stuk.be');
        $this->title = new Title('STUK');

        $this->organizerCreatedWithUniqueWebsite = new OrganizerCreatedWithUniqueWebsite(
            $this->id,
            $this->website,
            $this->title
        );
    }

    /**
     * @test
     */
    public function it_imports_from_udb2_actors()
    {
        $cdbXml = $this->getCdbXML('organizer_with_email.cdbxml.xml');

        $this->scenario
            ->when(
                function () use ($cdbXml) {
                    return Organizer::importFromUDB2(
                        '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    );
                }
            )
            ->then(
                [
                    new OrganizerImportedFromUDB2(
                        '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                        $cdbXml,
                        'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                    ),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_create_new_organizers()
    {
        $this->scenario
            ->when(
                function () {
                    return Organizer::create(
                        $this->id,
                        $this->website,
                        $this->title
                    );
                }
            )
            ->then(
                [
                    $this->organizerCreatedWithUniqueWebsite,
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_set_an_initial_address_and_update_it_later_if_changed()
    {
        $initialAddress = new Address(
            'Wetstraat 1',
            '1000',
            'Brussel',
            'BE'
        );

        $updatedAddress = new Address(
            'Martelarenlaan 1',
            '3000',
            'Leuven',
            'BE'
        );

        $this->scenario
            ->given([$this->organizerCreatedWithUniqueWebsite])
            ->when(
                function (Organizer $organizer) use ($initialAddress, $updatedAddress) {
                    $organizer->updateAddress($initialAddress);

                    // Update the address twice with the same value so we can
                    // test it doesn't get recorded the second time.
                    $organizer->updateAddress($updatedAddress);
                    $organizer->updateAddress($updatedAddress);
                }
            )
            ->then(
                [
                    new AddressUpdated($this->id, $initialAddress),
                    new AddressUpdated($this->id, $updatedAddress),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_set_an_initial_contact_point_if_not_empty_and_can_update_it_later_if_changed()
    {
        $emptyContactPoint = new ContactPoint();

        $initialContactPoint = new ContactPoint(['0444/444444']);
        $updatedContactPoint = new ContactPoint(['0455/454545'], ['foo@bar.com']);

        $this->scenario
            ->given([$this->organizerCreatedWithUniqueWebsite])
            ->when(
                function (Organizer $organizer) use ($emptyContactPoint, $initialContactPoint, $updatedContactPoint) {
                    // Should NOT record an event.
                    $organizer->updateContactPoint($emptyContactPoint);

                    // Update the contact point twice with the same value so we
                    // can test it doesn't get recorded the second time.
                    $organizer->updateContactPoint($initialContactPoint);
                    $organizer->updateContactPoint($initialContactPoint);

                    $organizer->updateContactPoint($updatedContactPoint);

                    // Should get recorded. It's empty but users should be able
                    // to remove contact point info.
                    $organizer->updateContactPoint($emptyContactPoint);
                }
            )
            ->then(
                [
                    new ContactPointUpdated($this->id, $initialContactPoint),
                    new ContactPointUpdated($this->id, $updatedContactPoint),
                    new ContactPointUpdated($this->id, $emptyContactPoint),
                ]
            );
    }

    /**
     * @test
     */
    public function it_can_be_deleted()
    {
        $this->scenario
            ->given(
                [
                    $this->organizerCreatedWithUniqueWebsite,
                ]
            )
            ->when(
                function (Organizer $organizer) {
                    $organizer->delete();
                }
            )
            ->then(
                [
                    new OrganizerDeleted($this->id),
                ]
            );
    }

    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Organizer::class;
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getCdbXML($filename)
    {
        return file_get_contents(__DIR__ . '/' . $filename);
    }
}
