<?php

use \Comodojo\RpcServer\Tests\CommonCases;
use \Comodojo\RpcServer\RpcServer;

class JsonRpcCommonTest extends CommonCases {

    protected $current_id = 0;

    protected function encodeRequest($method, $parameters) {

        $this->current_id = rand(1,1000);

        $call = array(
            "jsonrpc" => "2.0",
            "method" => $method,
            "params" => $parameters,
            "id" => $this->current_id
        );

        return json_encode($call);

    }

    protected function decodeResponse($received) {

        $response = json_decode($received);

        if ( $response->jsonrpc != "2.0" ) throw new Exception("Server replies with invalid jsonrpc version");

        if ( $response->id != $this->current_id ) throw new Exception("Server replies wiht invalid ID");

        return $response->result;

    }

    protected function decodeError($received) {

        $response = json_decode($received);

        if ( $response->jsonrpc != "2.0" ) throw new Exception("Server replies with invalid jsonrpc version");

        if ( $response->id != $this->current_id ) throw new Exception("Server replies wiht invalid ID");

        return $response->error;

    }

    protected function setUp() {

        $this->server = new RpcServer(RpcServer::JSONRPC);

    }

    protected function tearDown() {

        unset($this->server);

    }

    public function testNotification() {

        $request = json_encode( array(
            "jsonrpc" => "2.0",
            "method" => 'system.listMethods'
        ) );

        $result = $this->server->setPayload($request)->serve();

        $this->assertNull($result);

    }

    public function testMulticallForbidden() {

        $request = $this->encodeRequest('system.multicall', array(array('test.method', array('test.parameter'))));

        $result = $this->server->setPayload($request)->serve();

        $decoded = json_decode($result);

        $this->assertEquals(-31000, $decoded->error->code);

    }

}
