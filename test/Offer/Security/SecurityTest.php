<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Offer\ReadModel\Permission\PermissionQueryInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\String\String as StringLiteral;

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
     * @var Security
     */
    private $security;

    protected function setUp()
    {
        $this->userIdentification = $this->getMock(UserIdentificationInterface::class);

        $this->permissionRepository = $this->getMock(PermissionQueryInterface::class);

        $this->security = new Security(
            $this->userIdentification,
            $this->permissionRepository
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
    public function it_returns_false_when_not_own_offer()
    {
        $this->mockGetId(new StringLiteral('userId'));

        $this->mockIsGodUser(false);

        $this->mockGetEditableOffers(['otherOfferId', 'andOtherOfferId']);

        $offerId = new StringLiteral('offerId');
        $allowsUpdate = $this->security->allowsUpdateWithCdbXml($offerId);

        $this->assertFalse($allowsUpdate);
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
}
