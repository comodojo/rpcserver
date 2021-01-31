<?php

use \Comodojo\RpcServer\RpcServer;
use \PHPUnit\Framework\TestCase;

class RpcServerTest extends TestCase {

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

    public function testInvalidDeclaration() {

        $this->expectException("\Exception");

        $server = new \Comodojo\RpcServer\RpcServer('yaml');

    }

}
