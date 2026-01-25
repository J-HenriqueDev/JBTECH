<?php

$xmlPath = 'c:\JBTech Dev\JBTECH\signed_dps.xml';
$xsdPath = 'c:\JBTech Dev\JBTECH\vendor\hadder\nfse-nacional\storage\schemes\DPS_v1.00.xsd';

if (!file_exists($xmlPath)) {
    die("XML file not found.\n");
}
if (!file_exists($xsdPath)) {
    die("XSD file not found.\n");
}

$dom = new DOMDocument();
$dom->load($xmlPath);

// Enable internal error handling
libxml_use_internal_errors(true);

if ($dom->schemaValidate($xsdPath)) {
    echo "XML is valid against the schema.\n";
} else {
    echo "XML is INVALID against the schema:\n";
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        echo " - Line {$error->line}: {$error->message}\n";
    }
    libxml_clear_errors();
}
