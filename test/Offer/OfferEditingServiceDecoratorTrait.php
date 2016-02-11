<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;

class OfferEditingServiceDecoratorTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OfferEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratee;

    /**
     * @var OfferEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trait;

    public function setUp()
    {
        $this->decoratee = $this->getMock(OfferEditingServiceInterface::class);

        // Only the abstract methods of the trait are mocked. All other methods
        // are available as actual implementations.
        $this->trait = $this->getMockForTrait(OfferEditingServiceDecoratorTrait::class);

        $this->trait->expects($this->any())
            ->method('getDecoratedEditingService')
            ->willReturn($this->decoratee);
    }

    /**
     * @test
     * @dataProvider editingServiceMethodDataProvider
     * @param string $method
     * @param array $arguments
     */
    public function it_delegates_each_method_to_the_decoratee($method, $arguments)
    {
        $this->decoratee->expects($this->once())
            ->method($method)
            ->willReturn(
                // The with() method doesn't accept an array of arguments, so
                // we have to manually verify that the correct arguments were
                // passed in the willReturn() method.
                function () use ($arguments) {
                    $actualArguments = func_get_args();
                    if ($actualArguments !== $arguments) {
                        $this->fail("Provided arguments don't match expected arguments.");
                    }
                    return null;
                }
            );

        // Call the decorated method on the trait.
        call_user_func_array(array($this->trait, $method), $arguments);
    }

    public function editingServiceMethodDataProvider()
    {
        return [
            [
                'addLabel',
                [
                    'offer-id-1',
                    new Label('foo'),
                ],
            ],
            [
                'deleteLabel',
                [
                    'offer-id-2',
                    new Label('bar'),
                ],
            ],
        ];
    }
}
