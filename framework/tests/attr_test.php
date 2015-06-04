<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Tests;

use mrbavii\Framework\Attr;

require __DIR__ . "/bootstrap.php";

/**
 * Unit test for mrbavii\Framework\Tests
 * @runTestsInSeparateProcess
 */
class AttrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the Attr can store named items
     */
    public function testAttr()
    {
        $data2 = array("key1" => "value", "key2" => 0);
        $attr2 = new Attr($data2);

        $data1 = array("key1" => $attr2, "key2" => "value2");
        $attr = new Attr($data1);

        $this->assertEquals("value", $attr->key1->key1);
        $this->assertEquals(0, $attr->key1->key2);
        $this->assertEquals("value2", $attr->key2);
    }
}


