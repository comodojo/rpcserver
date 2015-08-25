<?php

class RpcMethodTest extends \PHPUnit_Framework_TestCase {

	public function testNewMethod() {

		$method = \Comodojo\RpcServer\RpcMethod::create("test.sum", function($params) {

			$a = $params->get('a');

			$b = $params->get('b');

			return (is_null($a) || is_null($b)) ? '42' : ($a + $b);

		})  ->setDescription("Sum two integers")
			->setReturnType('string')
			->addSignature()
			->addParameter('int','a')
			->addParameter('int','b')
			->setReturnType('int');

        $this->assertInstanceOf('\Comodojo\RpcServer\RpcMethod', $method);

        $this->assertEquals("test.sum",$method->getName());

        $this->assertEquals("Sum two integers",$method->getDescription());

        $this->assertInstanceOf('Closure', $method->getCallback());

        $this->assertNull($method->getMethod());

        $signatures = $method->getSignatures();

        $this->assertInternalType('array', $signatures);

        $this->assertCount(2, $signatures);

        $this->assertCount(1, $signatures[0]);

        $this->assertCount(3, $signatures[1]);

        $parameters = $method->getParameters();

        $this->assertInternalType('array', $parameters);

        $this->assertArrayHasKey("a", $parameters);

        $this->assertArrayHasKey("b", $parameters);

        $parameters = $method->getParameters('NUMERIC');

        $this->assertCount(2,$parameters);

        $this->assertEquals('int',$method->getReturnType());

        $method->deleteParameter('b');

        $parameters = $method->getParameters('NUMERIC');

        $this->assertCount(1,$parameters);

        $method->selectSignature(0);

        $this->assertEquals('string',$method->getReturnType());

        $signature = $method->getSignature();

        $this->assertCount(1,$signature);

        $method->deleteSignature(1);

        $signatures = $method->getSignatures();

        $this->assertCount(1,$signatures);

	}

}