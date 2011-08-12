<?php
if ($firephpEnabled) {
    require_once("FirePHPCore/FirePHP.class.php");
    $firephp = FirePHP::getInstance(true);
    $firephp->setEnabled(true);

} else {
    class MockLogger {
        function log() { }
    }
    $firephp = new MockLogger();
}
