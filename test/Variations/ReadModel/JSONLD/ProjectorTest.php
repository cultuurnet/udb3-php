<?php

namespace CultuurNet\UDB3\Variations\ReadModel\JSONLD;

use CultuurNet\UDB3\Event\ReadModel\JsonDocument;
use CultuurNet\UDB3\Variations\Model\Events\DescriptionEdited;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use ValueObjects\Identity\UUID;

class ProjectorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->repository = $this->getMock(
            RepositoryInterface::class
        );

        $this->projector = new Projector(
            $this->repository
        );
    }

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $projector;


    /**
     * @test
     */
    public function it_updates_the_variation_description_when_edited()
    {
        $variationId = new Id(UUID::generateAsString());
        $description = new Description('This is a new description');
        $descriptionEdited = new DescriptionEdited($variationId, $description);

        $variation = new JsonDocument(
            (string) $variationId,
            json_encode([
                'description' => [
                    'nl' => 'The variation description'
                ]
            ])
        );

        $updatedVariation = new JsonDocument(
            (string) $variationId,
            json_encode([
                'description' => [
                    'nl' => 'This is a new description'
                ]
            ])
        );

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with((string) $variationId)
            ->willReturn($variation);

        $this->repository
            ->expects(($this->once()))
            ->method('save')
            ->with($this->callback(
                function (JsonDocument $jsonDocument) use ($updatedVariation) {
                    return $updatedVariation == $jsonDocument;
                }
            ));

        $this->projector->applyDescriptionEdited($descriptionEdited);
    }
}
