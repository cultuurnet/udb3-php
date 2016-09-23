<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Mock\Commands\AddLabel;
use CultuurNet\UDB3\Offer\Mock\Commands\TranslateTitle;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class SecurityWithLabelPrivacyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $securityDecoratee;

    /**
     * @var UserIdentificationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userIdentification;

    /**
     * @var ReadRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelReadRepository;

    /**
     * @var SecurityWithLabelPrivacy
     */
    private $securityWithLabelPrivacy;

    /**
     * @var AddLabel
     */
    private $addLabel;

    protected function setUp()
    {
        $this->securityDecoratee = $this->getMock(SecurityInterface::class);

        $this->userIdentification = $this->getMock(
            UserIdentificationInterface::class
        );

        $this->labelReadRepository = $this->getMock(
            ReadRepositoryInterface::class
        );

        $this->securityWithLabelPrivacy = new SecurityWithLabelPrivacy(
            $this->securityDecoratee,
            $this->userIdentification,
            $this->labelReadRepository
        );

        $this->addLabel = new AddLabel('6a475eb2-04dd-41e3-95d1-225a1cd511f1', new Label('bibliotheekweek'));
    }

    /**
     * @test
     */
    public function it_delegates_allows_update_with_cdbxml_to_decoratee()
    {
        $offerId = new StringLiteral('3650cf00-aa8a-4cf3-a928-a01c2eb3b0d8');

        $this->securityDecoratee->method('allowsUpdateWithCdbXml')
            ->with($offerId);

        $this->securityWithLabelPrivacy->allowsUpdateWithCdbXml($offerId);
    }

    /**
     * @test
     */
    public function it_delegates_is_authorized_to_decoratee_when_not_a_label_command()
    {
        $translateTitle = new TranslateTitle(
            'cc9b975b-80e3-47db-ae77-8a930e453232',
            new Language('nl'),
            new StringLiteral('Hallo wereld')
        );

        $this->securityDecoratee->method('isAuthorized')
            ->with($translateTitle);

        $this->securityWithLabelPrivacy->isAuthorized($translateTitle);
    }

    /**
     * @test
     */
    public function a_god_user_can_use_all_labels()
    {
        $this->mockIsGodUser(true);

        $this->assertTrue(
            $this->securityWithLabelPrivacy->isAuthorized($this->addLabel)
        );
    }

    /**
     * @test
     */
    public function a_normal_user_can_only_use_labels_he_is_allowed_to_use()
    {
        $this->mockIsGodUser(false);

        $this->userIdentification->method('getId')
            ->willReturn(new StringLiteral('82650413-baf2-4257-a25b-d25dc18999dc'));

        $this->labelReadRepository->method('canUseLabel')
            ->willReturn(true);

        $this->assertTrue(
            $this->securityWithLabelPrivacy->isAuthorized($this->addLabel)
        );
    }

    /**
     * @param $isGodUser
     */
    private function mockIsGodUser($isGodUser)
    {
        $this->userIdentification->method('isGodUser')
            ->willReturn($isGodUser);
    }
}