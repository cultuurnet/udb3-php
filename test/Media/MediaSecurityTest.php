<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use CultuurNet\UDB3\Security\SecurityInterface;

class MediaSecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseSecurity;

    /**
     * @var MediaSecurity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaSecurity;

    public function setUp()
    {
        $this->baseSecurity = $this->createMock(SecurityInterface::class);
        $this->mediaSecurity = new MediaSecurity($this->baseSecurity);
    }

    /**
     * @test
     */
    public function it_should_always_authorize_command_with_media_upload_permission()
    {
        /** @var AuthorizableCommandInterface|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(AuthorizableCommandInterface::class);
        $command
            ->expects($this->once())
            ->method('getPermission')
            ->willReturn(Permission::MEDIA_UPLOADEN());

        $authorized = $this->mediaSecurity->isAuthorized($command);

        $this->assertEquals(true, $authorized);
    }

    /**
     * @test
     */
    public function it_should_delegate_authorization_of_non_media_commands_to_the_decorated_security()
    {
        /** @var AuthorizableCommandInterface|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(AuthorizableCommandInterface::class);
        $command
            ->expects($this->once())
            ->method('getPermission')
            ->willReturn(Permission::GEBRUIKERS_BEHEREN());

        $this->baseSecurity
            ->expects($this->once())
            ->method('isAuthorized')
            ->with($command);

        $this->mediaSecurity->isAuthorized($command);
    }
}
