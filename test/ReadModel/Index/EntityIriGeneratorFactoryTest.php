<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use PHPUnit\Framework\TestCase;

class EntityIriGeneratorFactoryTest extends TestCase
{
    private $baseUrl = 'http://hello.world';

    /**
     * @test
     * @dataProvider typedUrlsProvider
     */
    public function it_should_generate_urls_for_each_entity_type(
        EntityType $entityType,
        $entityId,
        $url
    ) {
        $factory = new EntityIriGeneratorFactory($this->baseUrl);
        $generator = $factory->forEntityType($entityType);
        $iri = $generator->iri($entityId);

        $this->assertEquals($url, $iri);
    }

    public function typedUrlsProvider()
    {
        return [
            [
                EntityType::EVENT(),
                'aef8d300-4dfa-48e1-8332-99d279c19b54',
                'http://hello.world/event/aef8d300-4dfa-48e1-8332-99d279c19b54',
            ],
            [
                EntityType::PLACE(),
                '11ac20cb-89a0-4cf2-8f8a-ccdec3417ec2',
                'http://hello.world/place/11ac20cb-89a0-4cf2-8f8a-ccdec3417ec2',
            ],
        ];
    }
}
