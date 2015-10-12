<?php

use \Comodojo\RpcServer\RpcServer;

class RpcServerTest extends \PHPUnit_Framework_TestCase {

    public function testSettersAndGetters() {

        $server = new RpcServer(RpcServer::XMLRPC);

        $result = $server->setProtocol(RpcServer::JSONRPC);

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcServer', $result);

        $result = $server->getProtocol();

        $this->assertEquals(RpcServer::JSONRPC, $result);

        $result = $server->setPayload('test_payload');

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcServer', $result);

        $result = $server->getPayload();

        $this->assertEquals('test_payload', $result);

        $result = $server->setEncoding('iso-8859-1');

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcServer', $result);

        $result = $server->getEncoding();

        $this->assertEquals('iso-8859-1', $result);

        $result = $server->setEncryption('mysupersecretkey');

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcServer', $result);

        $result = $server->getEncryption();

        $this->assertEquals('mysupersecretkey', $result);

    }

    /**
     * @expectedException        \Exception
     */
    public function testInvalidDeclaration() {

        $server = new \Comodojo\RpcServer\RpcServer('yaml');

    }

}