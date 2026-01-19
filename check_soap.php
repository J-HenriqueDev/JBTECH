<?php
require __DIR__ . '/vendor/autoload.php';

try {
    if (defined('NFePHP\NFe\Common\SOAP_1_2')) {
        echo "Constant SOAP_1_2 is defined: " . constant('NFePHP\NFe\Common\SOAP_1_2') . "\n";
    } else {
        echo "Constant SOAP_1_2 is NOT defined.\n";
    }
} catch (\Throwable $e) {
    echo "Error checking constant: " . $e->getMessage() . "\n";
}
