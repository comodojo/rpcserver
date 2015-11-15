<?php

class NullLoggerTest extends \PHPUnit_Framework_TestCase {

    public function testLogger() {

        $logger = new \Comodojo\RpcServer\Util\NullLogger();

        $message = "null message";

        $context = array("null", "context");

        $result = $logger->emergency($message, $context);

        $this->assertNull($result);

        $result = $logger->alert($message, $context);

        $this->assertNull($result);

        $result = $logger->critical($message, $context);

        $this->assertNull($result);

        $result = $logger->error($message, $context);

        $this->assertNull($result);

        $result = $logger->warning($message, $context);

        $this->assertNull($result);

        $result = $logger->notice($message, $context);

        $this->assertNull($result);

        $result = $logger->info($message, $context);

        $this->assertNull($result);

        $result = $logger->debug($message, $context);

        $this->assertNull($result);

        $result = $logger->log(250, $message, $context);

        $this->assertNull($result);

    }

}
