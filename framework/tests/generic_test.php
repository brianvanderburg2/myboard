<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Tests;

use mrbavii\Framework\Generic;

require __DIR__ . "/bootstrap.php";

/**
 * Unit test for mrbavii\Framework\Generic
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the Generic can store named items
     */
    public function testGenericStore()
    {
        $data2 = array("key1" => "value", "key2" => 0);
        $g2 = new Generic($data2);

        $data1 = array("key1" => $g2, "key2" => "value2");
        $g = new Generic($data1);

        $this->assertEquals("value", $g->key1->key1);
        $this->assertEquals(0, $g->key1->key2);
        $this->assertEquals("value2", $g->key2);

        // Test setting values indirectly (g->key1 is g2)
        $g2->key3 = 175;
        $this->assertEquals(175, $g->key1->key3);
    }

    /**
     * Test that Generic can call methods.
     */
    public function testGenericCall()
    {
        $fn = function($obj, $value) {
            return $obj->key1 * $value;
        };

        $data = array("key1" => 25, "fn" => $fn);
        $g = new Generic($data);

        $this->assertEquals(75, $g->fn(3));
        $this->assertEquals(175, $g->fn(7));

        // Test changing values
        $g->key1 = 15;
        $this->assertEquals(45, $g->fn(3));
        $this->assertEquals(60, $g->fn(4));

        // Test adding a function
        $g->fn2 = function($obj, $value) {
            return $obj->key1 + $value;
        };

        $this->assertEquals(25, $g->fn2(10));
        $this->assertEquals(17, $g->fn2(2));
    }

}


