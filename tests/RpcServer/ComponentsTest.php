<?php

use \Comodojo\Foundation\Logging\Manager as LogManager;

class ComponentsTest extends \PHPUnit_Framework_TestCase {

    public function testCapabilities() {

        $logger = LogManager::create('rpcserver', false)->getLogger();

        $cap = new \Comodojo\RpcServer\Component\Capabilities($logger);

        $add = $cap->add('spacetrip','https://comodojo.org/spacetrip.html',0.1);

        $this->assertTrue($add);

        $add = $cap->add('spacetrip','https://comodojo.org/fakespacetrip.html',0.2);

        $this->assertFalse($add);

        $get = $cap->get();

        $this->assertInternalType('array', $get);

        $this->assertCount(1, $get);

        $this->assertInternalType('array', $get['spacetrip']);

        $get = $cap->get("spacetrip");

        $this->assertInternalType('array', $get);

        $this->assertEquals('https://comodojo.org/spacetrip.html', $get['specUrl']);

        $this->assertEquals(0.1, $get['specVersion']);

        $delete = $cap->delete('spacetrip');

        $this->assertTrue($delete);

        $delete = $cap->delete('fakespacetrip');

        $this->assertFalse($delete);

        $nullget = $cap->get();

        $this->assertInternalType('array', $nullget);

        $this->assertCount(0, $nullget);

    }

    public function testErrors() {

        $logger = LogManager::create('rpcserver', false)->getLogger();

        $err = new \Comodojo\RpcServer\Component\Errors($logger);

        $add = $err->add(-90000,'Test Error');

        $this->assertTrue($add);

        $add = $err->add(-90000,'Test Error');

        $this->assertFalse($add);

        $get = $err->get(-90000);

        $this->assertEquals('Test Error', $get);

        $get = $err->get(-32098);

        $this->assertEquals('Server Error', $get);

        $get = $err->get(-32601);

        $this->assertEquals('Unknown Error', $get);

        $get = $err->get(-30000);

        $this->assertEquals('Unknown Error', $get);

        $delete = $err->delete(-90000);

        $this->assertTrue($delete);

        $getall = $err->get();

        $this->assertInternalType('array', $getall);

    }

    public function testMethods() {

        $logger = LogManager::create('rpcserver', false)->getLogger();

        $met = new \Comodojo\RpcServer\Component\Methods($logger);

        $one = \Comodojo\RpcServer\RpcMethod::create("test.one", function($params) { return $params->get(); })
            ->setDescription("Test Method One")
            ->setReturnType('struct');

        $two = \Comodojo\RpcServer\RpcMethod::create("test.two", function($params) { return $params->get(); })
            ->setDescription("Test Method Two")
            ->setReturnType('struct');

        $add = $met->add($one);

        $this->assertTrue($add);

        $add = $met->add($two);

        $this->assertTrue($add);

        $add = $met->add($two);

        $this->assertFalse($add);

        $get = $met->get('test.one');

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcMethod', $get);

        $get = $met->get();

        $this->assertInternalType('array', $get);

        $this->assertCount(2, $get);

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcMethod', $get['test.one']);

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcMethod', $get['test.two']);

        $delete = $met->delete('test.one');

        $this->assertTrue($delete);

        $delete = $met->delete('test.bla');

        $this->assertFalse($delete);

        $get = $met->get();

        $this->assertInternalType('array', $get);

        $this->assertCount(1, $get);

    }

}
