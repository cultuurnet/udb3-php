<?php

namespace CultuurNet\UDB3\Security;

class AnonymousUserIdentificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnonymousUserIdentification
     */
    private $anonymousUserIdentification;

    protected function setUp()
    {
        $this->anonymousUserIdentification = new AnonymousUserIdentification();
    }

    /**
     * @test
     */
    public function it_is_not_a_god_user()
    {
        $this->assertFalse($this->anonymousUserIdentification->isGodUser());
    }

    /**
     * @test
     */
    public function it_has_id_of_null()
    {
        $this->assertNull($this->anonymousUserIdentification->getId());
    }
}
