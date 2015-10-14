<?php

use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\RpcServer\Tests\CommonCases;
use \Comodojo\RpcServer\RpcServer;
use \Comodojo\RpcServer\RpcMethod;

class XmlRpcMulticallTest extends \PHPUnit_Framework_TestCase {

    protected function encodeRequest($data) {

        $encoder = new XmlrpcEncoder();

        return $encoder->encodeMulticall($data);

    }

    protected function decodeResponse($received) {

        $decoder = new XmlrpcDecoder();

        return $decoder->decodeResponse($received);

    }

    protected function setUp() {
        
        $this->server = new RpcServer(RpcServer::XMLRPC);

        $method = RpcMethod::create("test.sum", function($params) {

            $a = $params->get('a');

            $b = $params->get('b');

            return ( is_null($a) || is_null($b) ) ? 42 : intval($a) + intval($b);

        })  ->setDescription("Sum two integers")
            ->setReturnType('int')
            ->addParameter('int','a')
            ->addParameter('int','b')
            ->addSignature()
            ->setReturnType('int');

        $this->server->methods()->add($method);
    
    }

    protected function tearDown() {

        unset($this->server);

    }

    public function testMulticall() {

        $data = array(
            array('test.sum', array(2,2)),
            'test.foo' => array(),
            array('test.sum', array(4,4)),
            array('test.sum', array())
        );

        $request = $this->encodeRequest($data);

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertEquals(4, $decoded[0]);

        $this->assertInternalType('array', $decoded[1]);

        $this->assertEquals(-32601, $decoded[1]['faultCode']);

        $this->assertEquals('Method not found', $decoded[1]['faultString']);

        $this->assertEquals(8, $decoded[2]);

        $this->assertEquals(42, $decoded[3]);

    }

    public function testRecursiveMulticall() {

        $data = array(
            array('test.sum', array(2,2)),
            array('system.multicall', array())
        );

        $request = $this->encodeRequest($data);

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(2, $decoded);

        $this->assertEquals(-31001, $decoded[1]['faultCode']);

        $this->assertEquals('Recursive system.multicall forbidden', $decoded[1]['faultString']);        


    }

}