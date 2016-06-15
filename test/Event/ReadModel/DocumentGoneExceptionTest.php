<?php

namespace CultuurNet\UDB3\Event\ReadModel;

class DocumentGoneExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_the_HTTP_GONE_status_code_by_default()
    {
        $exception = new DocumentGoneException();

        $this->assertEquals(410, $exception->getCode());
    }
}
