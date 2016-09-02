<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\ContactPoint;

class AbstractUpdateContactPointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractUpdateContactPoint|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateContactPoint;

    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var ContactPoint
     */
    protected $contactPoint;

    public function setUp()
    {
        $this->itemId = 'Foo';
        $this->contactPoint = new ContactPoint(
            array('0123456789'),
            array('foo@bar.com'),
            array('http://foo.bar'),
            'type'
        );

        $this->updateContactPoint = $this->getMockForAbstractClass(
            AbstractUpdateContactPoint::class,
            array($this->itemId, $this->contactPoint)
        );
    }

    /**
     * @test
     */
    public function it_can_return_its_properties()
    {
        $contactPoint = $this->updateContactPoint->getContactPoint();
        $expectedContactPoint = new ContactPoint(
            array('0123456789'),
            array('foo@bar.com'),
            array('http://foo.bar'),
            'type'
        );

        $this->assertEquals($expectedContactPoint, $contactPoint);

        $itemId = $this->updateContactPoint->getId();
        $expectedItemId = 'Foo';

        $this->assertEquals($expectedItemId, $itemId);
    }
}
