<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2\Actor;

class QualifiesAsOrganizerSpecificationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var QualifiesAsOrganizerSpecification
     */
    private $qualifiesAsOrganizerSpecification;

    public function setUp()
    {
        $this->qualifiesAsOrganizerSpecification = new QualifiesAsOrganizerSpecification();
    }

    /**
     * @test
     */
    public function it_is_satisified_by_actors_with_organizer_actortype_category()
    {
        $actor = new \CultureFeed_Cdb_Item_Actor();

        $this->assertFalse(
            $this->qualifiesAsOrganizerSpecification->isSatisfiedBy($actor)
        );

        $categories = new \CultureFeed_Cdb_Data_CategoryList();
        $categories->add(
            new \CultureFeed_Cdb_Data_Category(
                \CultureFeed_Cdb_Data_Category::CATEGORY_TYPE_ACTOR_TYPE,
                '8.11.0.0.0',
                'Organisator(en)'
            )
        );

        $actor->setCategories($categories);

        $this->assertTrue(
            $this->qualifiesAsOrganizerSpecification->isSatisfiedBy($actor)
        );
    }
}
