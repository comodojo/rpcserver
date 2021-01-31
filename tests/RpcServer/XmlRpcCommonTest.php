<?php

use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\RpcServer\Tests\CommonCases;
use \Comodojo\RpcServer\RpcServer;
use \PHPUnit\Framework\TestCase;

class XmlRpcCommonTest extends CommonCases {

    protected function encodeRequest($method, $parameters) {

        $encoder = new XmlrpcEncoder();

        return $encoder->encodeCall($method, $parameters);

    }

    protected function decodeResponse($received) {

        $decoder = new XmlrpcDecoder();

        return $decoder->decodeResponse($received);

    }

    protected function setUp(): void {

        $this->server = new RpcServer(RpcServer::XMLRPC);

    }

    protected function tearDown(): void {

        unset($this->server);

    }

}
