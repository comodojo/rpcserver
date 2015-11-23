# comodojo/rpcserver

[![Build Status](https://api.travis-ci.org/comodojo/rpcserver.png)](http://travis-ci.org/comodojo/rpcserver) [![Latest Stable Version](https://poser.pugx.org/comodojo/rpcserver/v/stable)](https://packagist.org/packages/comodojo/rpcserver) [![Total Downloads](https://poser.pugx.org/comodojo/rpcserver/downloads)](https://packagist.org/packages/comodojo/rpcserver) [![Latest Unstable Version](https://poser.pugx.org/comodojo/rpcserver/v/unstable)](https://packagist.org/packages/comodojo/rpcserver) [![License](https://poser.pugx.org/comodojo/rpcserver/license)](https://packagist.org/packages/comodojo/rpcserver) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comodojo/rpcserver/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/comodojo/rpcserver/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/comodojo/rpcserver/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/comodojo/rpcserver/?branch=master)

This library provides a framework and transport independent implementation of XML and JSON(2.0) RPC server.

It is designed to work in combination with a REST framework that could handle the transport side (such as [Comodojo dispatcher](https://github.com/comodojo/dispatcher.framework)).

Main features are:
- full XMLRPC and JSONRPC(2.0) protocols support, including multicall and batch requests
- embedded introspection methods
- [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compliant logging
- payload decoding/econding and encryption
- support multiple signatures per method

Following capabilities are supported out of the box:

- [xmlrpc](http://www.xmlrpc.com/spec)
- [system.multicall](http://www.xmlrpc.com/discuss/msgReader$1208)
- [introspection](http://phpxmlrpc.sourceforge.net/doc-2/ch10.html)
- [nil](http://www.ontosys.com/xml-rpc/extensions.php)
- [faults_interop](http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php)
- [json-rpc](http://www.jsonrpc.org/specification)

Additional capabilities could be implemented [extending the library](#extending-the-library).

## Installation

Install [composer](https://getcomposer.org/), then:

`` composer require comodojo/rpcserver ``

## Basic usage

Quick and dirty example, without a framework that mediates RPC requests:

```php

// get the raw request payload (POST)
$payload = file_get_contents('php://input');

try {

	// create a RpcServer instance (i.e. JSON)
    $server = new \Comodojo\RpcServer\RpcServer(\Comodojo\RpcServer\RpcServer::JSONRPC);
    
    // create a method (using lambda functions)
    $method = \Comodojo\RpcServer\RpcMethod::create("example.sum", function($params) {
    
        $a = $params->get('a');

        $b = $params->get('b');

        return intval($a) + intval($b);

    })->setDescription("Sum two integers")
    ->setReturnType('int')
    ->addParameter('int','a')
    ->addParameter('int','b');
    
    // register method into server
    $is_method_in = $server->methods()->add($method);
    
    // set the payload
    $server->setPayload($request);
    
    // serve the request
    $result = $server->serve();

} catch (\Exception $e) {
	
	/* something did not work :( */

}

echo $result;

```

### Creating methods

The `\Comodojo\RpcServer\RpcMethod` class should be used to create RPC methods to inject into server.

It requires basically a method name and a callable, provided as labmda function, named function or couple class::method.

Parameters can be added using `addParameter()` method. Multiple signatures could be specified using `addSignature()`.

For example, to create a **my.method** RPC method mapped to `\My\RpcClass::mymethod()` that has two different signatures:

```php

// create a method using class::method pattern
$method = \Comodojo\RpcServer\RpcMethod::create("my.method", "\My\RpcClass", "mymethod")
    // provide a description for the method
    ->setDescription("My method")
    // set the return type (default: undefined)
    ->setReturnType('boolean')
    // add a second signature
    ->addSignature()
    // set the return type for second signature
    ->setReturnType('boolean')
    // add expected parameters (if any) for second signature
    ->addParameter('int','a')
    ->addParameter('int','b');

```

Signatures are automatically matched by server as well as received parameters; if a request does not match any valid signature, a "Invalid params" (-32602) error is returned back to client.

The `\My\RpcClass::mymethod()` should expect a `\Comodojo\RpcServer\Request\Parameters` object that provides received parameters, server properties and logging interface.

```php

class RpcClass {

    public function mymethod($params) {
    
        $a = $params->get('a');
        
        $b = $params->get('b');
        
        $logger = $params->logger();
        
        $current_rpc_protocol = $params->protocol();
        
        $return = array($a, $b);
        
        $logger->info("Current protocol: ".$current_rpc_protocol.", returned value in context", $return);
        
        return $return;
    
    }

}

```

### Server properties

The `\Comodojo\RpcServer\RpcServer` should be created specifying the RPC protocol (using constant `RpcServer::JSONRPC` or `RpcServer::XMLRPC`).

The optional parameter *$logger* expects an implementation of `\Psr\Log\LoggerInterface` and enable internal logging.

```php

// create the server
$server = new RpcServer(RpcServer::XMLRPC);

// (optional) set encoding (default to *utf-8*)
$server->setEncoding('ISO-8859-1');

```

Once created, server expects a payload, and starts processing it when `serve()` is invoked:

```php

$result = $server->setPayload($request)->serve();

```

A non-standard PSK encryption using AES could be enabled specifying key with `$server->setEncryption($key)`; the [Comodojo RpcClient](https://github.com/comodojo/rpcclient) package implements a compatible client.

## Extending the library

RPC methods, server capabilities and standard errors can be added/removed using:

- `$server->methods()`
- `$server->capabilities()`
- `$server->errors()`

For example, to add a server capability:

```php

$my_capability = $server->capabilities()->add("my.capability", "http://url.to.my/capability", 1.0);

```

## Documentation

- [API](https://api.comodojo.org/libs/Comodojo/RpcServer.html)

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

`` comodojo/rpcserver `` is released under the MIT License (MIT). Please see [License File](LICENSE) for more information.