<?php

namespace CultuurNet\UDB3\ReadModel\Index;

use PHPUnit\Framework\TestCase;

class EntityTypeTest extends TestCase
{
    /**
     * Data provider with the expected allowed values for
     * EntityType::getByName().
     */
    public function allowedValues()
    {
        return [
            ['EVENT'],
            ['PLACE'],
        ];
    }

    /**
     * @test
     * @dataProvider allowedValues
     */
    public function it_accepts_a_limited_set_of_values($allowedValue)
    {
        EntityType::getByName($allowedValue);
    }

    /**
     * @test
     */
    public function it_does_not_accept_other_values()
    {
        $this->expectException(\InvalidArgumentException::class);
        EntityType::getByName('foo');
    }

    /**
     * @test
     */
    public function it_can_give_back_native_scalar_string_values()
    {
        $this->assertEquals(
            'event',
            EntityType::EVENT()->toNative()
        );

        $this->assertEquals(
            'place',
            EntityType::PLACE()->toNative()
        );
    }
}
