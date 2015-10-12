<?php

use \Comodojo\RpcServer\Tests\CommonCases;
use \Comodojo\RpcServer\RpcServer;

class JsonRpcBatchRequestTest extends PHPUnit_Framework_TestCase {

    protected $current_id = array();

    protected function encodeRequest($method, $parameters) {

        $rand = rand(1,1000);

        $this->current_id[] = $rand; 

        $call = array(
            "jsonrpc" => "2.0",
            "method" => $method, 
            "params" => $parameters,
            "id" => $rand
        );

        return $call;

    }

    protected function decodeResponse($received) {

        $responses = json_decode($received);

        $return = array();

        foreach ($responses as $response) {
            
            if ( $response->jsonrpc != "2.0" ) throw new Exception("Server replies with invalid jsonrpc version");

            if ( !in_array($response->id, $this->current_id) ) throw new Exception("Server replies wiht invalid ID");

            $return[] = $response->result;

        }

        return $return;

    }

    protected function packRequest($request) {

        return json_encode($request);

    }

    protected function setUp() {
        
        $this->server = new RpcServer(RpcServer::JSONRPC);
    
    }

    protected function tearDown() {

        unset($this->server);

    }

    public function testSystemMethodSignatureBatch() {

        $requests = array(
            $this->encodeRequest('system.methodSignature', array('system.listMethods')),
            $this->encodeRequest('system.methodHelp', array('system.listMethods')),
            $this->encodeRequest('system.listMethods', array())
        );

        $result = $this->server->setPayload( $this->packRequest($requests) )->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertInternalType('array', $decoded);

        $this->assertInternalType('array', $decoded[0]);

        $this->assertEquals('array', $decoded[0][0]);

        $this->assertInternalType('string', $decoded[1]);

        $this->assertEquals('This method lists all the methods that the RPC server knows how to dispatch', $decoded[1]);

        $this->assertInternalType('array', $decoded[2]);

        $this->assertCount(5, $decoded[2]);

    }

    public function testMixedNotification() {

        $requests = array(
            $this->encodeRequest('system.methodSignature', array('system.listMethods')),
            $this->encodeRequest('system.listMethods', array()),
            array(
                "jsonrpc" => "2.0",
                "method" => 'system.listMethods'
            )
        );

        $result = $this->server->setPayload( $this->packRequest($requests) )->serve();

        $decoded = json_decode($result);

        $this->assertCount(2, $decoded);

    }

    public function testMixedError() {

        $requests = array(
            $this->encodeRequest('system.methodSignature', array('system.listMethods')),
            $this->encodeRequest('test.foo', array())
        );

        $result = $this->server->setPayload( $this->packRequest($requests) )->serve();

        $decoded = json_decode($result);

        $this->assertCount(2, $decoded);

        $this->assertEquals('array', $decoded[0]->result[0]);

        $this->assertEquals(-32601, $decoded[1]->error->code);

    }

}