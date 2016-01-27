<?php

namespace CultuurNet\UDB3\Media;

use ValueObjects\Identity\UUID;
use ValueObjects\String\String;

class SimplePathGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_generate_the_file_path_for_a_given_id_and_extentions()
    {
        $generator = new SimplePathGenerator();
        $fileId = new UUID('de305d54-75b4-431b-adb2-eb6b9e546014');
        $extension = new String('png');
        $expectedPath = 'de305d54-75b4-431b-adb2-eb6b9e546014.png';

        $path = $generator->path($fileId, $extension);

        $this->assertEquals($expectedPath, $path);
    }
}
