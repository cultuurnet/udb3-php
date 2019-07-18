<?php

namespace SearchAPI2;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\SearchAPI2\ResultSetPullParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ValueObjects\Number\Integer;
use ValueObjects\Web\Url;

class ResultSetPullParserTest extends TestCase
{
    /**
     * @var IriGeneratorInterface | MockObject
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface | MockObject
     */
    protected $placeIriGenerator;

    /**
     * @var ResultSetPullParser
     */
    protected $resultSetPullParser;

    public function setUp()
    {
        $this->eventIriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->placeIriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->resultSetPullParser = new ResultSetPullParser(
            new \XMLReader(),
            $this->eventIriGenerator,
            $this->placeIriGenerator
        );
    }

    /**
     * @test
     */
    public function it_extracts_totalItems_and_member_ids_from_a_cbxml_result_set()
    {
        $cdbxml = file_get_contents(
            __DIR__ . '/search_results.xml'
        );

        $this->eventIriGenerator
            ->expects($this->exactly(5))
            ->method('iri')
            ->withConsecutive(
                ['590174eb-5577-4b49-8bc2-4b619a948c56'],
                ['70d24706-6e23-406c-9b54-445f5249ae6b'],
                ['2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45'],
                ['409cca2b-d5bb-4f53-9312-de22bdbbcbb2'],
                ['40836aa3-9fcb-4672-8f69-6394fc0873f2']
            )
            ->willReturnCallback(
                function ($id) {
                    return 'http://du.de/event/' . $id;
                }
            );

        $this->placeIriGenerator
            ->expects($this->exactly(4))
            ->method('iri')
            ->withConsecutive(
                ['9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c'],
                ['d9725327-cbec-4bb8-bc56-9f3f7761b716'],
                ['ee08000a-ccfa-4675-93ef-a0dc02ae1be4'],
                ['c2a8a22d-e4c5-41a9-bbee-0d7f6e5e194d']
            )
            ->willReturnCallback(
                function ($id) {
                    return 'http://du.de/place/' . $id;
                }
            );

        $resultSet = $this->resultSetPullParser->getResultSet($cdbxml);

        $expectedResultSet = new Results(
            OfferIdentifierCollection::fromArray(
                [
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/590174eb-5577-4b49-8bc2-4b619a948c56'),
                        '590174eb-5577-4b49-8bc2-4b619a948c56',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c'),
                        '9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/d9725327-cbec-4bb8-bc56-9f3f7761b716'),
                        'd9725327-cbec-4bb8-bc56-9f3f7761b716',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/70d24706-6e23-406c-9b54-445f5249ae6b'),
                        '70d24706-6e23-406c-9b54-445f5249ae6b',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45'),
                        '2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/409cca2b-d5bb-4f53-9312-de22bdbbcbb2'),
                        '409cca2b-d5bb-4f53-9312-de22bdbbcbb2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/40836aa3-9fcb-4672-8f69-6394fc0873f2'),
                        '40836aa3-9fcb-4672-8f69-6394fc0873f2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/ee08000a-ccfa-4675-93ef-a0dc02ae1be4'),
                        'ee08000a-ccfa-4675-93ef-a0dc02ae1be4',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/c2a8a22d-e4c5-41a9-bbee-0d7f6e5e194d'),
                        'c2a8a22d-e4c5-41a9-bbee-0d7f6e5e194d',
                        OfferType::PLACE()
                    ),
                ]
            ),
            new Integer(1820)
        );

        $this->assertEquals($expectedResultSet, $resultSet);
    }

    /**
     * @test
     */
    public function it_should_set_the_identifier_iri_to_the_external_url_when_provided()
    {
        $cdbxml = file_get_contents(
            __DIR__ . '/search_results_with_external_urls.xml'
        );

        $this->eventIriGenerator
            ->expects($this->exactly(4))
            ->method('iri')
            ->withConsecutive(
                ['70d24706-6e23-406c-9b54-445f5249ae6b'],
                ['2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45'],
                ['409cca2b-d5bb-4f53-9312-de22bdbbcbb2'],
                ['40836aa3-9fcb-4672-8f69-6394fc0873f2']
            )
            ->willReturnCallback(
                function ($id) {
                    return 'http://du.de/event/' . $id;
                }
            );

        $this->placeIriGenerator
            ->expects($this->exactly(2))
            ->method('iri')
            ->withConsecutive(
                ['d9725327-cbec-4bb8-bc56-9f3f7761b716'],
                ['ee08000a-ccfa-4675-93ef-a0dc02ae1be4']
            )
            ->willReturnCallback(
                function ($id) {
                    return 'http://du.de/place/' . $id;
                }
            );

        $resultSet = $this->resultSetPullParser->getResultSet($cdbxml);

        $expectedResultSet = new Results(
            OfferIdentifierCollection::fromArray(
                [
                    new IriOfferIdentifier(
                        Url::fromNative('http://www.omd.de/events/590174eb-5577-4b49-8bc2-4b619a948c56'),
                        '590174eb-5577-4b49-8bc2-4b619a948c56',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://www.omd.de/places/9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c'),
                        '9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/d9725327-cbec-4bb8-bc56-9f3f7761b716'),
                        'd9725327-cbec-4bb8-bc56-9f3f7761b716',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/70d24706-6e23-406c-9b54-445f5249ae6b'),
                        '70d24706-6e23-406c-9b54-445f5249ae6b',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45'),
                        '2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/409cca2b-d5bb-4f53-9312-de22bdbbcbb2'),
                        '409cca2b-d5bb-4f53-9312-de22bdbbcbb2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/event/40836aa3-9fcb-4672-8f69-6394fc0873f2'),
                        '40836aa3-9fcb-4672-8f69-6394fc0873f2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://du.de/place/ee08000a-ccfa-4675-93ef-a0dc02ae1be4'),
                        'ee08000a-ccfa-4675-93ef-a0dc02ae1be4',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        Url::fromNative('http://www.omd.de/places/c2a8a22d-e4c5-41a9-bbee-0d7f6e5e194d'),
                        'c2a8a22d-e4c5-41a9-bbee-0d7f6e5e194d',
                        OfferType::PLACE()
                    ),
                ]
            ),
            new Integer(1820)
        );

        $this->assertEquals($expectedResultSet, $resultSet);
    }
}
