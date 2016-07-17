<?php

namespace CultuurNet\UDB3\Role\Commands;

use CultuurNet\UDB3\Role\MissingContentTypeException;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UpdateRoleRequestDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateRoleRequestDeserializer
     */
    private $deserializer;

    /**
     * @var string
     */
    private $roleId;

    public function setUp()
    {
        $this->deserializer = new UpdateRoleRequestDeserializer();
        $this->roleId = '153c7e92-4903-11e6-beb8-9e71128cae77';
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_the_request_has_no_content_type()
    {
        $request = $this->makeRequest();

        $this->setExpectedException(MissingContentTypeException::class);

        $this->deserializer->deserialize($request, $this->roleId);
    }

    /**
     * @test
     */
    public function it_can_rename_a_role_with_the_correct_content_type_set()
    {
        $request = $this->makeRequest();
        $request->headers->set('Content-Type', 'application/ld+json;domain-model=RenameRole');

        $command = $this->deserializer->deserialize($request, $this->roleId);

        $expectedCommand = new RenameRole(
            new UUID($this->roleId),
            new StringLiteral('editRole')
        );

        $this->assertEquals($expectedCommand, $command);
    }

    /**
     * @test
     */
    public function it_can_set_a_constraint_with_the_correct_content_type_set()
    {
        $request = $this->makeRequest();
        $request->headers->set('Content-Type', 'application/ld+json;domain-model=SetConstraint');
        $request->server->set('Content-Type', 'application/ld+json;domain-model=SetConstraint');

        //var_dump($request);

        $command = $this->deserializer->deserialize($request, $this->roleId);

        $expectedCommand = new SetConstraint(
            new UUID($this->roleId),
            new StringLiteral('category_flandersregion_name:"Regio Aalst"')
        );

        $this->assertEquals($expectedCommand, $command);
    }

    public function makeRequest()
    {
        $content = $this->getJson('update_role.json');
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod('PATCH');

        return $request;
    }

    private function getJson($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return $json;
    }
}