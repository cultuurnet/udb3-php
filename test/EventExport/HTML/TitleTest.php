<?php

namespace CultuurNet\UDB3\EventExport\HTML;

use \InvalidArgumentException;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_be_at_least_one_character_long()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new Title('');
    }
}
