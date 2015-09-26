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

        //var_export($decoded);

        $this->assertEquals(4, $decoded);

    }

}
