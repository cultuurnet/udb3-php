<?php

namespace spec\CultuurNet\UDB3\SearchAPI2;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Search\Results;
use CultuurNet\UDB3\SearchAPI2\ResultSetPullParser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ValueObjects\Number\Integer;

/**
 * @mixin \CultuurNet\UDB3\SearchAPI2\ResultSetPullParser
 */
class ResultSetPullParserSpec extends ObjectBehavior
{
    public function it_is_initializable_with_a_XMLReader_and_a_IriGeneratorInterface(
        \XMLReader $xmlReader,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->beConstructedWith($xmlReader, $iriGenerator);
        $this->shouldHaveType(ResultSetPullParser::class);
    }

    public function it_extracts_totalItems_and_member_ids_from_a_cbxml_result_set(
        IriGeneratorInterface $iriGenerator
    ) {
        $iriGenerator->iri(
            '40836aa3-9fcb-4672-8f69-6394fc0873f2'
        )->willReturnArgument();
        $iriGenerator->iri(
            '590174eb-5577-4b49-8bc2-4b619a948c56'
        )->willReturnArgument();
        $iriGenerator->iri(
            '9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c'
        )->willReturnArgument();
        $iriGenerator->iri(
            'd9725327-cbec-4bb8-bc56-9f3f7761b716'
        )->willReturnArgument();
        $iriGenerator->iri(
            '70d24706-6e23-406c-9b54-445f5249ae6b'
        )->willReturnArgument();
        $iriGenerator->iri(
            '2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45'
        )->wilLReturnArgument();
        $iriGenerator->iri(
            '409cca2b-d5bb-4f53-9312-de22bdbbcbb2'
        )->willReturnArgument();
        $iriGenerator->iri(
            'ee08000a-ccfa-4675-93ef-a0dc02ae1be4'
        )->wilLReturnArgument();


        $this->beConstructedWith(new \XMLReader(), $iriGenerator);
        $cdbxml = file_get_contents(
            __DIR__ . '/ResultSetPullParserSpec.search.xml'
        );
        $this
            ->getResultSet($cdbxml)
            ->shouldBeLike(
                new Results(
                    array(
                        array(
                            '@id' => '590174eb-5577-4b49-8bc2-4b619a948c56'
                        ),
                        array(
                            '@id' => '9b60002a-9671-4b91-a2ad-5ccf8fbf7e5c',
                        ),
                        array(
                            '@id' => 'd9725327-cbec-4bb8-bc56-9f3f7761b716',
                        ),
                        array(
                            '@id' => '70d24706-6e23-406c-9b54-445f5249ae6b',
                        ),
                        array(
                            '@id' => '2c86bd2d-686a-41e8-a1fc-6fe99c9d6b45',
                        ),
                        array(
                            '@id' => '409cca2b-d5bb-4f53-9312-de22bdbbcbb2',
                        ),
                        array(
                            '@id' => '40836aa3-9fcb-4672-8f69-6394fc0873f2'
                        ),
                        array(
                            '@id' => 'ee08000a-ccfa-4675-93ef-a0dc02ae1be4',
                        ),
                    ),
                    new Integer(1820)
                )
            );
    }
}
