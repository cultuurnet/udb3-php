<?php

namespace CultuurNet\UDB3;

class EntityNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_the_HTTP_NOT_FOUND_status_code_by_default()
    {
        $exception = new EntityNotFoundException();

        $this->assertEquals(404, $exception->getCode());
    }
}
