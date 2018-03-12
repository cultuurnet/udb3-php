<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    public function emptyStringValues()
    {
        return array(
            array(''),
            array(' '),
            array('   '),
        );
    }

    /**
     * @test
     * @dataProvider emptyStringValues()
     * @param string $emptyStringValue
     */
    public function it_can_not_be_empty($emptyStringValue)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Title can not be empty.'
        );
        new Title($emptyStringValue);
    }

    /**
     * @test
     */
    public function it_should_be_creatable_from_an_udb3_model_title()
    {
        $udb3ModelTitle = new \CultuurNet\UDB3\Model\ValueObject\Text\Title('foo bar');

        $expected = new Title('foo bar');
        $actual = Title::fromUdb3ModelTitle($udb3ModelTitle);

        $this->assertEquals($expected, $actual);
    }
}
