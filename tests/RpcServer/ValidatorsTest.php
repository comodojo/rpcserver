<?php

use \Comodojo\RpcServer\Util\DataValidator;

class ValidatorsTest extends \PHPUnit_Framework_TestCase {

    public function testValidators() {

        $goods = array(
            "int" => 42,
            "double" => 42.42,
            "boolean" => true,
            "base64" => "dGhpc2lzYXRlc3QK",
            //"dateTime.iso8601" => date('c'),
            "string" => "test",
            "array" => array(0,1,2),
            "struct" => array("this" => "is", "a" => "test"),
            "null" => null
        );

        $bads = array(
            "int" => "42",
            "double" => 42,
            "boolean" => 2,
            "base64" => false,
            "dateTime.iso8601" => date('r'),
            "string" => 1,
            "array" => array("test"=>0,1,2),
            "struct" => array(0,1,2),
            "null" => "null"
        );

        foreach ($goods as $kind => $value) {
            $result = DataValidator::validate($kind, $value);
            $this->assertTrue($result);
            $result = DataValidator::validate("undefined", $value);
            $this->assertTrue($result);
        }

        foreach ($bads as $kind => $value) {
            $result = DataValidator::validate($kind, $value);
            $this->assertFalse($result);
        }

    }

}
