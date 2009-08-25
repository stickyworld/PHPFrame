<h1>PHPFrame Database Subpackage Examples</h1>

<h2>Code</h2>

<pre style="border:1px solid #990000; padding:20px; background-color: #FFFFCC;">

</pre>

<?php
include_once "PHPFrame.php";

$feature = new PHPFrame_Addons_FeatureInfo();
echo '<h2>Object to be inserted</h2>';
var_dump($feature);

$mapper = new PHPFrame_Mapper(
    "PHPFrame_Addons_FeatureInfo", 
    null, 
    PHPFrame_Mapper::STORAGE_XML, 
    false, 
    "/Users/lupomontero/Desktop".DS."domain.objects"
);

// Show objects before insert
echo '<h2>Collection BEFORE insert</h2>';
foreach ($mapper->find() as $item) {
    var_dump($item);
}

// Insert new object
$mapper->insert($feature);

// Show updated collection
echo '<h2>Collection AFTER insert</h2>';
foreach ($mapper->find() as $item) {
    var_dump($item);
}
