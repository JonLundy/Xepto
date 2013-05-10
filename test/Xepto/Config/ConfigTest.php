<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testHasOne() {

        $object = new \Xepto\Config(['one' => 'two']) ;
        $this->assertTrue(
            $object->one == 'two'
		);
    }
}
?>
