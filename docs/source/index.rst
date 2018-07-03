comodojo/rpcserver documentation
================================

.. _comodojo/dispatcher: https://github.com/comodojo/dispatcher
.. _PSR-3: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
.. _xmlrpc: http://www.xmlrpc.com/spec
.. _system.multicall: http://www.xmlrpc.com/discuss/msgReader$1208
.. _introspection: http://phpxmlrpc.sourceforge.net/doc-2/ch10.html
.. _nil: http://www.ontosys.com/xml-rpc/extensions.php
.. _faults_interop: http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php
.. _json-rpc: http://www.jsonrpc.org/specification

This library provides a framework (and transport) independent XML and JSON(2.0) RPC server.

It is designed to work in combination with a REST framework that could handle the transport side (such as `comodojo/dispatcher`_).

Main features are:

- full XMLRPC and JSONRPC(2.0) protocols support, including multicall and batch requests
- embedded introspection methods
- `PSR-3`_ compliant logging
- payload decoding/econding and encryption
- support for multiple signatures per method

Following capabilities are supported out of the box:

- `xmlrpc`_
- `system.multicall`_
- `introspection`_
- `nil`_
- `faults_interop`_
- `json-rpc`_

Additional capabilities could be implemented :ref:`extending-the-library`.

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   install
   basicusage
   methods
   server
   extending
