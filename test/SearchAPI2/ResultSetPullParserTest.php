<?php

namespace SearchAPI2;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\SearchAPI2\ResultSetPullParser;
use ValueObjects\Number\Integer;

class ResultSetPullParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IriGeneratorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventIriGenerator;

    /**
     * @var IriGeneratorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $placeIriGenerator;

    /**
     * @var ResultSetPullParser
     */
    protected $resultSetPullParser;

    public function setUp()
    {
        $this->eventIriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->placeIriGenerator = $this->getMock(IriGeneratorInterface::class);
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
                    return 'event/' . $id;
                }
            );

        $this->placeIriGenerator
            ->expects($this->exactly(3))
            ->method('iri')
            ->withConsecutive(
                ['9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c'],
                ['d9725327-cbec-4bb8-bc56-9f3f7761b716'],
                ['ee08000a-ccfa-4675-93ef-a0dc02ae1be4']
            )
            ->willReturnCallback(
                function ($id) {
                    return 'place/' . $id;
                }
            );

        $resultSet = $this->resultSetPullParser->getResultSet($cdbxml);

        $expectedResultSet = new Results(
            OfferIdentifierCollection::fromArray(
                [
                    new IriOfferIdentifier(
                        'event/590174eb-5577-4b49-8bc2-4b619a948c56',
                        '590174eb-5577-4b49-8bc2-4b619a948c56',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        'place/9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c',
                        '9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        'place/d9725327-cbec-4bb8-bc56-9f3f7761b716',
                        'd9725327-cbec-4bb8-bc56-9f3f7761b716',
                        OfferType::PLACE()
                    ),
                    new IriOfferIdentifier(
                        'event/70d24706-6e23-406c-9b54-445f5249ae6b',
                        '70d24706-6e23-406c-9b54-445f5249ae6b',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        'event/2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45',
                        '2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        'event/409cca2b-d5bb-4f53-9312-de22bdbbcbb2',
                        '409cca2b-d5bb-4f53-9312-de22bdbbcbb2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        'event/40836aa3-9fcb-4672-8f69-6394fc0873f2',
                        '40836aa3-9fcb-4672-8f69-6394fc0873f2',
                        OfferType::EVENT()
                    ),
                    new IriOfferIdentifier(
                        'place/ee08000a-ccfa-4675-93ef-a0dc02ae1be4',
                        'ee08000a-ccfa-4675-93ef-a0dc02ae1be4',
                        OfferType::PLACE()
                    ),
                ]
            ),
            new Integer(1820)
        );

        $this->assertEquals($expectedResultSet, $resultSet);
    }
}
