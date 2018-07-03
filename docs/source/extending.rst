.. _extending-the-library:

Extending the library
=====================

Beside RPC methods, server capabilities and errors can be added or removed using dedicated server methods.

Server Capabilities
-------------------

The ``RpcServer::getCapabilites`` allows access to capabilities manager that can be used to modify standard supported capabilities.

For example, to add a new capability:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcServer;

    // init the server
    $server = new RpcServer(RpcServer::JSONRPC);

    // ad a new capability
    $capabilities = $server->getCapabilities();
    $capabilities->add("my.capability", "http://url.to.my/capability", 1.0);

Custom Errors
-------------

Errors can be managed using the ``RpcServer::getErrors`` method.

For example, to add a new error:

.. code-block:: php
   :linenos:

    <?php

    use \Comodojo\RpcServer\RpcServer;

    // init the server
    $server = new RpcServer(RpcServer::JSONRPC);

    // ad a new capability
    $errors = $server->getErrors();
    $errors->add(-31010, "Transphasic torpedo was banned by the United Federation of Planets");
