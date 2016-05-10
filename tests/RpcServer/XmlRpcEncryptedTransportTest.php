<?php

use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Comodojo\RpcServer\RpcServer;

class XmlRpcEncryptedTransportTest extends \PHPUnit_Framework_TestCase {

    protected $key = "solongandthanksforallthefish";

    protected function encodeRequest($method, $parameters) {

        $encoder = new XmlrpcEncoder();

        $data = $encoder->encodeCall($method, $parameters);

        $aes = new Crypt_AES();

        $aes->setKey($this->key);

        return 'comodojo_encrypted_request-'.base64_encode($aes->encrypt($data));

    }

    protected function decodeResponse($received) {

        $aes = new Crypt_AES();

        $aes->setKey($this->key);

        $data = $aes->decrypt(base64_decode(substr($received, 28)));

        $decoder = new XmlrpcDecoder();

        return $decoder->decodeResponse($data);

    }

    protected function setUp() {

        $this->server = new RpcServer(RpcServer::XMLRPC);

        $this->server->setEncryption($this->key);

    }

    protected function tearDown() {

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

        $this->assertInternalType('string', $decoded);

        $this->assertEquals('This method lists all the methods that the RPC server knows how to dispatch', $decoded);

    }

}
