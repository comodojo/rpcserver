.. _server:

Using the RPC Server
====================

.. _comodojo/rpcclient: https://github.com/comodojo/rpcclient

The class ``\Comodojo\RpcServer\RpcServer`` realizes the core server component.

.. note:: As already mentioned, the server component does not provide transport management but just the logic to understand and serve RPC requests.

    Other frameworks or custom implementations can be used to mediates requests using HTTP, sockets or any other message-delivery transport.

An RPC Server should be created specifying the desired RPC protocol; constants ``RpcServer::JSONRPC`` and ``RpcServer::XMLRPC`` are available to setup server correctly.

The optional parameter *$logger* expects an implementation of ``\Psr\Log\LoggerInterface`` and implicitly enable internal logging.

Once created, server expects a payload, and starts processing it when ``RpcServer::serve`` method is invoked:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcServer;

    // create the server
    $server = new RpcServer(RpcServer::XMLRPC);

    // (optional) set encoding (default to *utf-8*)
    $server->setEncoding('ISO-8859-1');

    // feed server with request's payload and start serving the request
    $result = $server->setPayload($request)->serve();

Encrypting communications
-------------------------

This package provides a *non-standard* PSK message-level encryption (using AES).

This working mode could be enabled specifying the pre shared key using ``RpcServer::setEncryption`` method.

.. note:: The only client that supports this communication mode is the one provided by `comodojo/rpcclient`_ package.
