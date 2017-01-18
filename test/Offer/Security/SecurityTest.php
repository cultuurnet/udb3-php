<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Offer\Commands\AuthorizableCommandInterface;
use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\StringLiteral\StringLiteral;

class SecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserIdentificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdentification;

    /**
     * @var PermissionQueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionRepository;

    /**
     * @var UserPermissionMatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userPermissionMatcher;

    /**
     * @var Security
     */
    private $security;

    protected function setUp()
    {
        $this->userIdentification = $this->createMock(
            UserIdentificationInterface::class
        );

        $this->permissionRepository = $this->createMock(
            PermissionQueryInterface::class
        );

        $this->userPermissionMatcher = $this->createMock(
            UserPermissionMatcherInterface::class
        );

        $this->security = new Security(
            $this->userIdentification,
            $this->permissionRepository,
            $this->userPermissionMatcher
        );
    }

    /**
     * @test
     */
    public function it_returns_false_for_user_with_missing_id()
    {
        $this->mockGetId();

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertFalse($allowsUpdate);
    }

    /**
     * @test
     */
    public function it_returns_true_for_god_user()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(true);

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertTrue($allowsUpdate);
    }

    /**
     * @test
     */
    public function it_returns_true_for_own_offer()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(false);

        $this->mockGetEditableOffers(['offerId', 'otherOfferId']);

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertTrue($allowsUpdate);
    }

    /**
     * @test
     */
    public function it_returns_false_when_not_own_offer_and_not_matching_user_permission()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(false);

        $this->mockGetEditableOffers(['otherOfferId', 'andOtherOfferId']);

        $this->mockItMatchesOffer(false);

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertFalse($allowsUpdate);
    }

    /**
     * @test
     */
    public function it_returns_true_when_not_own_offer_but_matching_user_permission()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(false);

        $this->mockGetEditableOffers(['otherOfferId', 'andOtherOfferId']);

        $this->mockItMatchesOffer(true);

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertTrue($allowsUpdate);
    }

    /**
     * @test
     */
    public function it_also_handles_authorizable_command()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(true);

        $authorizableCommand = $this->createMock(AuthorizableCommandInterface::class);
        $authorizableCommand->method('getItemId')
            ->willReturn('offerId');

        $allowsUpdate = $this->security->isAuthorized($authorizableCommand);

        $this->assertTrue($allowsUpdate);
    }

    /**
     * @param StringLiteral|null $userId
     */
    private function mockGetId(StringLiteral $userId = null)
    {
        $this->userIdentification->method('getId')
            ->willReturn($userId);
    }

    /**
     * @param bool $isGodUser
     */
    private function mockIsGodUser($isGodUser)
    {
        $this->userIdentification->method('isGodUser')
            ->willReturn($isGodUser);
    }

    /**
     * @param string[] $editableOffers
     */
    private function mockGetEditableOffers($editableOffers)
    {
        $this->permissionRepository->method('getEditableOffers')
            ->willReturn($editableOffers);
    }

    /**
     * @param bool $matches
     */
    private function mockItMatchesOffer($matches)
    {
        $this->userPermissionMatcher->method('itMatchesOffer')
            ->willReturn($matches);
    }
}
