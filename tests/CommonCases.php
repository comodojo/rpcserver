<?php namespace Comodojo\RpcServer\Tests;

use \PHPUnit\Framework\TestCase;

abstract class CommonCases extends TestCase {

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

        $this->assertIsArray( $decoded[0]);

        $this->assertIsArray( $decoded[1]);

        $this->assertCount(3, $decoded[0]);

        $this->assertCount(1, $decoded[1]);

    }

    public function testSystemMethodHelp() {

        $request = $this->encodeRequest('system.methodHelp', array('system.listMethods'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertIsString($decoded);

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

    public function testMethodNotFoundError() {

        $request = $this->encodeRequest('test.sum', array(2,2));

        $result = $this->server->setPayload($request)->serve();

        if ( $this->server->getProtocol() == \Comodojo\RpcServer\RpcServer::XMLRPC ) {

            $decoded = $this->decodeResponse($result);

            $this->assertIsArray( $decoded);

            $this->assertCount(2, $decoded);

            $this->assertEquals(-32601, $decoded['faultCode']);

            $this->assertEquals('Method not found', $decoded['faultString']);

        } else {

            $decoded = $this->decodeError($result);

            $this->assertEquals(-32601, $decoded->code);

            $this->assertEquals('Method not found', $decoded->message);

        }

    }

    public function testCustomError() {

        $method = \Comodojo\RpcServer\RpcMethod::create("test.exception", function($params) {

            $error = $params->errors()->get(-32602);

            throw new \Comodojo\Exception\RpcException($error, -32602);

        })->setDescription("Test Method")
        ->setReturnType('string');

        $this->server->methods()->add($method);

        $request = $this->encodeRequest('test.exception', array());

        $result = $this->server->setPayload($request)->serve();

        if ( $this->server->getProtocol() == \Comodojo\RpcServer\RpcServer::XMLRPC ) {

            $decoded = $this->decodeResponse($result);

            $this->assertIsArray( $decoded);

            $this->assertCount(2, $decoded);

            $this->assertEquals(-32602, $decoded['faultCode']);

            $this->assertEquals('Invalid params', $decoded['faultString']);

        } else {

            $decoded = $this->decodeError($result);

            $this->assertEquals(-32602, $decoded->code);

            $this->assertEquals('Invalid params', $decoded->message);

        }

    }

    public function testEqualLengthMultipleSignatureMethod () {

        $method = \Comodojo\RpcServer\RpcMethod::create("test.multisignature", function($params) {

            $a = $params->get('a');

            return is_int($a) ? 'integer' : 'string';

        })  ->setDescription("Test multiple, equal lenght, signatures")
            ->setReturnType('string')
            ->addParameter('int','a')
            ->addSignature()
            ->setReturnType('string')
            ->addParameter('string','a');

        $result = $this->server->methods()->add($method);

        $this->assertTrue($result);

        $request = $this->encodeRequest('test.multisignature', array(2));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertEquals('integer', $decoded);

        $request = $this->encodeRequest('test.multisignature', array('test'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertEquals('string', $decoded);

    }

    public function testAdditionalArgumentsMethod() {

        $one = 10;

        $two = 20;

        $lambda = function($params, $b, $c) {

            $a = $params->get('a');

            return $a+$b+$c;

        };

        $method = \Comodojo\RpcServer\RpcMethod::create("test.additionalarguments", $lambda, $one, $two)
            ->setDescription("Test extra attributes forwarded to callback")
            ->setReturnType('string')
            ->addParameter('int','a');

        $result = $this->server->methods()->add($method);

        $this->assertTrue($result);

        $request = $this->encodeRequest('test.additionalarguments', array(12));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertEquals(42, $decoded);

    }

}
