<?php namespace Comodojo\RpcServer\Tests;

abstract class CommonCases extends \PHPUnit_Framework_TestCase {

    protected $server;

    abstract protected function encodeRequest($method, $parameters);

    abstract protected function decodeResponse($received);

    public function testSystemListMethods() {

        $request = $this->encodeRequest('system.listMethods', array());

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(5, $decoded);

    }

    public function testSystemGetCapabilities() {

        $request = $this->encodeRequest('system.getCapabilities', array());

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(6, (array)$decoded);

    }

    public function testSystemMethodSignature() {

        $request = $this->encodeRequest('system.methodSignature', array('system.listMethods'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $decoded = (array)$decoded;

        $this->assertCount(1, $decoded);

        $this->assertEquals('array', $decoded[0]);

        $request = $this->encodeRequest('system.methodSignature', array('system.methodSignature'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $decoded = (array)$decoded;

        $this->assertCount(2, $decoded);

        $this->assertEquals('array', $decoded[0]);

        $this->assertEquals('string', $decoded[1]);

        $method = \Comodojo\RpcServer\RpcMethod::create("test.sum", function($params) {

            $a = $params->get('a');

            $b = $params->get('b');

            return ( is_null($a) || is_null($b) ) ? 42 : intval($a) + intval($b);

        })  ->setDescription("Sum two integers")
            ->setReturnType('int')
            ->addParameter('int','a')
            ->addParameter('int','b')
            ->addSignature()
            ->setReturnType('int');

        $result = $this->server->methods()->add($method);

        $request = $this->encodeRequest('system.methodSignature', array('test.sum'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $decoded = (array)$decoded;

        $this->assertCount(2, $decoded);

        $this->assertInternalType('array', $decoded[0]);

        $this->assertInternalType('array', $decoded[1]);

        $this->assertCount(3, $decoded[0]);

        $this->assertCount(1, $decoded[1]);

    }

    public function testSystemMethodHelp() {

        $request = $this->encodeRequest('system.methodHelp', array('system.listMethods'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertInternalType('string', $decoded);

        $this->assertEquals('This method lists all the methods that the RPC server knows how to dispatch', $decoded);

    }

    public function testCapabilities() {

        $this->server->capabilities()->add('spacetrip','https://comodojo.org/spacetrip.html',0.1);

        $capabilities = $this->server->capabilities()->get();

        $this->assertCount(7, $capabilities);

        $request = $this->encodeRequest('system.getCapabilities', array());

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(7, (array)$decoded);

        $this->server->capabilities()->delete('spacetrip');

        $this->server->capabilities()->delete('faults_interop');

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(5, (array)$decoded);

    }

    public function testAddMethod() {

        $method = \Comodojo\RpcServer\RpcMethod::create("test.sum", function($params) {

            $a = $params->get('a');

            $b = $params->get('b');

            return intval($a) + intval($b);

        })  ->setDescription("Sum two integers")
            ->setReturnType('int')
            ->addParameter('int','a')
            ->addParameter('int','b');

        $result = $this->server->methods()->add($method);

        $this->assertTrue($result);

        $request = $this->encodeRequest('test.sum', array(2,2));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertEquals(4, $decoded);

    }

}
