<?php
require "PHPFrame.php";

// Read config file in current directory
$config = new PHPFrame_Config("phpframe.ini");

foreach ($config as $key=>$value) {
    echo $key.': '.$value."\n";
}