<?php

namespace CultuurNet\UDB3\Organizer;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\ContactPoint;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreated;
use CultuurNet\UDB3\Organizer\Events\OrganizerCreatedWithUniqueWebsite;
use CultuurNet\UDB3\Organizer\Events\OrganizerDeleted;
use CultuurNet\UDB3\Organizer\Events\OrganizerImportedFromUDB2;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class OrganizerTest extends AggregateRootScenarioTestCase
{
    /**
     * Returns a string representing the aggregate root
     *
     * @return string AggregateRoot
     */
    protected function getAggregateRootClass()
    {
        return Organizer::class;
    }

    private function getCdbXML($filename)
    {
        return file_get_contents(
            __DIR__ . $filename
        );
    }

    /**
     * @test
     */
    public function it_imports_from_udb2_actors()
    {
        $cdbXml = $this->getCdbXML(
            '/organizer_with_email.cdbxml.xml'
        );

        $this->scenario
            ->when(function () use ($cdbXml) {
                return Organizer::importFromUDB2(
                    '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                );
            })
            ->then([
                new OrganizerImportedFromUDB2(
                    '404EE8DE-E828-9C07-FE7D12DC4EB24480',
                    $cdbXml,
                    'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL'
                )
            ]);
    }

    /**
     *
     */
    public function it_can_create_new_organizers()
    {
        $id = '123';
        $website = Url::fromNative('http://www.stuk.be');
        $title = new Title('Het Stuk');
        $addresses = [new Address('$street', '$postalCode', '$locality', '$country')];
        $phones = ['050/123'];
        $emails = ['test@test.be', 'test2@test.be'];
        $urls = ['http://www.google.be'];
        $contactPoint = new ContactPoint($phones, $emails, $urls);

        $organizerCreated = new OrganizerCreatedWithUniqueWebsite(
            $id,
            $website,
            $title,
            $addresses,
            $contactPoint
        );

        $this->scenario
            ->when(function ($id, $website, $title, $addresses, $contactPoint) {
                return Organizer::create(
                    $id,
                    $website,
                    $title,
                    $addresses,
                    $contactPoint
                );
            })
            ->then([$organizerCreated]);
    }

    /**
     * @test
     */
    public function it_can_be_deleted()
    {
        $id = '123';

        $this->scenario
            ->given(
                [
                    new OrganizerCreatedWithUniqueWebsite(
                        $id,
                        Url::fromNative('http://www.stuk.be'),
                        new Title('Foo'),
                        [],
                        new ContactPoint([], [], [])
                    )
                ]
            )
            ->when(
                function (Organizer $organizer) {
                    $organizer->delete();
                }
            )
            ->then(
                [
                    new OrganizerDeleted($id)
                ]
            );
    }
}
