<?php
require_once __DIR__ . '/tcpdf.php'; // Path to your original TCPDF file
//require_once __DIR__ . '/src/Config/TCPDF_LimePDF.php'; 

// Fully qualified class name
$className = 'LimePDF\\TCPDF';

// Create reflection
$refClass = new ReflectionClass($className);

// Loop properties
foreach ($refClass->getProperties() as $prop) {
    $name = $prop->getName();
    $ucName = ucfirst($name);
    echo "<br />";
    echo "public function get{$ucName}() {\n";
    echo "<br />";
    echo " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return \$this->{$name};\n";
    echo "<br />";
    echo "}\n\n";
    echo "<br /><br />";
    echo "public function set{$ucName}(\$value) {\n";
    echo "<br />";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$this->{$name} = \$value;\n";
    echo "<br />";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return \$this;\n";
    echo "<br />";
    echo "}\n\n";
}
