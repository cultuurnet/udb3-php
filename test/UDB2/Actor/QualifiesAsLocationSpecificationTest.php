<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

class QualifiesAsLocationSpecificationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var QualifiesAsLocationSpecification
     */
    private $qualifiesAsLocationSpecification;

    public function setUp()
    {
        $this->qualifiesAsLocationSpecification = new QualifiesAsLocationSpecification();
    }

    /**
     * @test
     */
    public function it_is_satisified_by_actors_with_location_actortype_category()
    {
        $actor = new \CultureFeed_Cdb_Item_Actor();

        $this->assertFalse(
            $this->qualifiesAsLocationSpecification->isSatisfiedBy($actor)
        );

        $categories = new \CultureFeed_Cdb_Data_CategoryList();
        $categories->add(
            new \CultureFeed_Cdb_Data_Category(
                \CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_ACTOR_TYPE,
                '8.15.0.0.0',
                'Locatie'
            )
        );

        $actor->setCategories($categories);

        $this->assertTrue(
            $this->qualifiesAsLocationSpecification->isSatisfiedBy($actor)
        );
    }
}
