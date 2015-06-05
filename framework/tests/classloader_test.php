<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Tests;

use mrbavii\Framework\ClassLoader;

require __DIR__ . "/bootstrap.php";


/**
 * Unit test for mrbavii\Framework\ClassLoader
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the tests
     */
    public function setUp()
    {
        ClassLoader::register(__NAMESPACE__ . "\\ClassLoader", __DIR__ . "/classloader");
    }

    /**
     * Test the class loader loads direct classes.
     */
    public function testLoader()
    {
        // Test class
        $classname = __NAMESPACE__ . "\\ClassLoader\\Class1";
        $this->assertFalse(class_exists($classname, FALSE));
        $this->assertTrue(class_exists($classname, TRUE));
    }

    /**
     * Test the class loader loads classes in sub namespaces
     */
    public function testSubNamespace()
    {
        // Ensure subnamepsaces are loaded fine
        $classname = __NAMESPACE__ . "\\ClassLoader\\SubNS\\Class1";
        $this->assertFalse(class_exists($classname, FALSE));
        $this->assertTrue(class_exists($classname, TRUE));
    }
}

