.. _methods:

Creating methods
================

The ``\Comodojo\RpcServer\RpcMethod`` class should can be used to create custom RPC methods to inject into server.

It requires basically a method name and a callable, provided as lambda function, named function or couple class::method.

Parameters can be added using ``RpcMethod::addParameter``; multiple signatures can be specified using the ``RpcMethod::addSignature`` method.

For example, to create a *my.method* RPC method mapped to ``\My\RpcClass::mymethod()`` that supports two different signatures:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcMethod;

    // create a method using class::method pattern
    $method = RpcMethod::create('my.method', '\My\RpcClass::mymethod')
        // provide a description for the method
        ->setDescription("My method")

        // for now on, parameters and return type will be associated
        // to the first (default) signature, until next addSignature() marker

        // set the return type (default: undefined)
        ->setReturnType('boolean')

        // start another signature, the second one
        ->addSignature()

        // set the return type for second signature
        ->setReturnType('boolean')

        // add expected parameters (if any) for second signature
        ->addParameter('int','a')
        ->addParameter('int','b');

.. note:: Signatures are automatically matched by the server as well as received parameters.

    If a request does not match any valid signature, an *Invalid params* (-32602) error is returned to the client.

Defining Callbacks
------------------

As in previous example, the ``\My\RpcClass::mymethod()`` has to be created to handle the request.

This method should expect a ``\Comodojo\RpcServer\Request\Parameters`` object that provides:

- received parameters (``Parameters::get``)
- server properties
    - capabilities (``Parameters::getCapabilities``)
    - methods (``Parameters::getMethods``)
    - errors (``Parameters::getErrors``)
- RPC protocol in use (``Parameters::getProtocol``)
- logging interface (``Parameters::getLogger``)

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\Request\Parameters;

    class RpcClass {

        public static function mymethod(Parameters $params) {

            // retrieve 'a' param
            $a = $params->get('a');

            // retrieve 'b' param
            $b = $params->get('b');

            // get current PSR-3 logger
            $logger = $params->getLogger();

            // get current protocol
            $current_rpc_protocol = $params->getProtocol();

            // log something...
            $logger->info("mymethod called, current protocol: $current_rpc_protocol, parameters in context", [$a, $b]);

            return $a === $b;

        }

    }

Injecting extra arguments
-------------------------

In case the callback method needs extra arguments in input, they should be specified as additional arguments in method declaration.

Server will transfer them when callback is fired.

As an example, a method declaration like:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcMethod;

    // create a method that transfer two additional arguments
    $method = RpcMethod::create(
        'my.method',
        '\My\RpcClass::mymethod',
        \My\Extra\Attribute $attribute,
        $another_attribute
    )
    ->setDescription("My method")
    ->setReturnType('boolean');

Will invoke a callback like:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\Request\Parameters;
    use \My\Extra\Attribute;

    class RpcClass {

        public static function mymethod(
            Parameters $params,
            Attribute $attribute,
            $another_attribute
        ) {

            // ... method internals

        }

    }
