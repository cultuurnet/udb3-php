<?php

namespace CultuurNet\UDB3\Offer\Security;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Mock\Commands\AddLabel;
use CultuurNet\UDB3\Offer\Mock\Commands\TranslateTitle;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Security\UserIdentificationInterface;
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

        $this->addLabel = new AddLabel('itemId', new Label('labelName'));
    }

    /**
     * @test
     */
    public function it_delegates_allows_update_with_cdbxml_to_decoratee()
    {
        $offerId = new StringLiteral('offerId');

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
            'itemId',
            new Language('nl'),
            new StringLiteral('translated Title')
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
            ->willReturn(new StringLiteral('userId'));

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
