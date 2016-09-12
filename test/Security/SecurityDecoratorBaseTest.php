<?php

namespace CultuurNet\UDB3\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use ValueObjects\String\String as StringLiteral;

class SecurityDecoratorBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratee;

    /**
     * @var SecurityDecoratorBase
     */
    private $decoratorBase;

    protected function setUp()
    {
        $this->decoratee = $this->getMock(SecurityInterface::class);

        $this->decoratee->method('allowsUpdateWithCdbXml')
            ->willReturn(true);

        $this->decoratee->method('isAuthorized')
            ->willReturn(true);

        $this->decoratorBase = $this->getMockForAbstractClass(
            SecurityDecoratorBase::class,
            [$this->decoratee]
        );
    }

    /**
     * @test
     */
    public function it_calls_allows_update_with_cdbxml_from_decoratee()
    {
        $this->decoratee->expects($this->once())
            ->method('allowsUpdateWithCdbXml');

        $this->decoratorBase->allowsUpdateWithCdbXml(new StringLiteral('offerId'));
    }

    /**
     * @test
     */
    public function it_returns_allows_update_with_cdbxml_result_from_decoratee()
    {
        $this->assertTrue($this->decoratorBase->allowsUpdateWithCdbXml(
            new StringLiteral('offerId')
        ));
    }

    /**
     * @test
     */
    public function it_calls_is_authorized_from_decoratee()
    {
        $this->decoratee->expects($this->once())
            ->method('isAuthorized');

        $command = $this->getMock(AuthorizableCommandInterface::class);
        $this->decoratorBase->isAuthorized($command);
    }

    /**
     * @test
     */
    public function it_returns_is_authorized_result_from_decoratee()
    {
        $command = $this->getMock(AuthorizableCommandInterface::class);

        $this->assertTrue($this->decoratorBase->isAuthorized($command));
    }
}
