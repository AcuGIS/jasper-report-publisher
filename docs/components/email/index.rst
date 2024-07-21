.. This is a comment. Note how any initial comments are moved by
   transforms to after the document title, subtitle, and docinfo.

.. demo.rst from: http://docutils.sourceforge.net/docs/user/rst/demo.txt

.. |EXAMPLE| image:: static/yi_jing_01_chien.jpg
   :width: 1em

**********************
Email
**********************
.. contents:: Table of Contents
Overview
==================

Jasper Publisher allows you to set SMTP credentials for email.

It also provides email HTML Template functionality.

SMTP Settings
=====================

SMTP settings are entered during setup.

They can also be added or edited at any time via /var/www/.muttrc

The contents should be as below::

  set copy = no
  set from = 'Jasper Publisher <you@yourdomain.com>'
  set smtp_url = 'smtp://you@yourdomain.com:Password@mail.yourdomain.com:587/'


Email HTML Templates
=====================


.. image:: Parameter-1.png

Enter the following information:

* Paramater Type	- Enter 'dropdown'
* Paramater Name - Display name of Paramater
* Paramater Values - For LOV type, enter a comma separated list of values
* Report Name - Select the report Parameter will be applied to.

In the example below, the Values are North America, South America, and Europe.

.. image:: JRI-Viewer-Param.png



Query Parameter
=====================

To add a Query parameter, click the "Add New" button at top.

.. image:: Parameter-1.png

Enter the following information:

* Paramater Type	- Enter 'query'
* Paramater Name - Display name of Paramater
* Paramater Values - Comma separated list of Parameters to be used
* Report Name - Select the report Parameter will be applied to.

In the example below, the Values we entered are the Jasper parameters Cost_Greater_Than and Cost_Less_Than.

.. image:: Parameter-2.png

Edit Parameter
===================
To edit a Parameter entry, click the Edit icon

Delete Parameter
===================
To delete a Parameter entry, click the Delete icon



