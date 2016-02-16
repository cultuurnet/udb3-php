<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Language;

class AbstractTranslatePropertyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractTranslatePropertyCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translatePropertyCommand;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Language
     */
    protected $language;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->language = new Language('en');

        $this->translatePropertyCommand = $this->getMockForAbstractClass(
            AbstractTranslatePropertyCommand::class,
            array($this->itemId, $this->language)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $this->translatePropertyCommand->expects($this->any())
            ->method('getLanguage')
            ->willReturn(new Language('en'));

        $language = $this->translatePropertyCommand->getLanguage();
        $expectedLanguage = new Language('en');

        $this->assertEquals($expectedLanguage, $language);

        $this->translatePropertyCommand->expects($this->any())
            ->method('getItemId')
            ->willReturn('Foo');

        $itemId = $this->translatePropertyCommand->getItemId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }
}
