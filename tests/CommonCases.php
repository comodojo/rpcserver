<?php namespace Comodojo\RpcServer\Tests;

abstract class CommonCases extends \PHPUnit_Framework_TestCase {

    protected $server;

    abstract protected function encodeRequest($method, $parameters);

    abstract protected function decodeResponse($received);

    public function testListMethods() {

        $request = $this->encodeRequest('system.listMethods', array());

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(5, $decoded);

    }

}
