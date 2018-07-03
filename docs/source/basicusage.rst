Basic Usage
===========

Following a quick and dirty example of lib basic usage, without a framework that mediates RPC requests.

.. note:: For more detailed informations, please see :ref:`server` and :ref:`methods` pages.

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcServer;
    use \Comodojo\RpcServer\RpcMethod;
    use \Exception;

    // get the raw request payload (using, for example, an HTTP::POST)
    $payload = file_get_contents('php://input');

    try {

        // create a RpcServer instance (i.e. JSON)
        $server = new RpcServer(RpcServer::JSONRPC);

        // create a method (using a lambda functions)
        $method = RpcMethod::create("example.sum", function($params) {
                $a = $params->get('a');
                $b = $params->get('b');
                return intval($a) + intval($b);
        })
        ->setDescription("Sum two integers")
        ->setReturnType('int')
        ->addParameter('int','a')
        ->addParameter('int','b');

        // register newly created method into server
        $server->getMethods()->add($method);

        // set the payload
        $server->setPayload($request);

        // serve the request
        $result = $server->serve();

    } catch (Exception $e) {

        /* something did not work :( */
        throw $e;

    }

    echo $result;
