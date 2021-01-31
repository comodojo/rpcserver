<?php

use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\RpcServer\RpcServer;
use \phpseclib3\Crypt\AES;
use \PHPUnit\Framework\TestCase;

class XmlRpcEncryptedTransportTest extends TestCase {

    protected $key = "solongandthanksforallthefish";

    protected function encodeRequest($method, $parameters) {

        $encoder = new XmlrpcEncoder();

        $data = $encoder->encodeCall($method, $parameters);

        $aes = new AES('ecb');

        $aes->setKey(md5($this->key));

        return 'comodojo_encrypted_request-'.base64_encode($aes->encrypt($data));

    }

    protected function decodeResponse($received) {

        $aes = new AES('ecb');

        $aes->setKey(md5($this->key));

        $data = $aes->decrypt(base64_decode(substr($received, 28)));

        $decoder = new XmlrpcDecoder();

        return $decoder->decodeResponse($data);

    }

    protected function setUp(): void {

        $this->server = new RpcServer(RpcServer::XMLRPC);

        $this->server->setEncryption($this->key);

    }

    protected function tearDown(): void {

        unset($this->server);

    }

    public function testTransportEncryption() {

        $request = $this->encodeRequest('system.getCapabilities', array());

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertCount(6, (array)$decoded);

        $request = $this->encodeRequest('system.methodHelp', array('system.listMethods'));

        $result = $this->server->setPayload($request)->serve();

        $decoded = $this->decodeResponse($result);

        $this->assertIsString($decoded);

        $this->assertEquals('This method lists all the methods that the RPC server knows how to dispatch', $decoded);

    }

}
