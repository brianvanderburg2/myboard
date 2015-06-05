<?php

namespace mrbavii\Framework\Tests;

// Require framework bootstrap
require __DIR__ . "/../bootstrap.php";

// Sometimes Text_Template isn"t found
if(!class_exists("Text_Template"))
{
    require "Text/Template.php";
}

