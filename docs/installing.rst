************
Installation
************

Installation is done via the install scripts located in the /installer directory.

System Requirements
=======================
* PostgreSQL 16
* PHP >= 8.1
* 2 GB RAM
* 5 GB Disk
* Tested on Ubuntu 22

Running the Installer
=======================

Clone or download the repoistory:

.. code-block:: console

    git clone https://github.com/AcuGIS/Jasper-Publisher
    mv Jasper-Publisher-master Jasper-Publisher

Navigate to /JasperPublisher and run the installers:

.. code-block:: console
 

   ./installer/postgres.sh
   ./installer/app-install.sh
   ./installer/jri-install.sh
   ./installer/jri-sample.sh

Optionally, run below to provision SSL using letsencrypt:

.. code-block:: console

   apt-get -y install python3-certbot-apache

   certbot --apache --agree-tos --email hostmaster@yourdomain.com --no-eff-email -d yourdomain.com


Navigate to https://yourdomain.com/admin/setup.php:

.. image:: _static/install-1.png

Enter the information for the PostgreSQL database you created:

.. image:: _static/install-screen-2.png

The installer will create the required objects in PostgreSQL

When the installer completes, you can log in using the email and password you selected above.

.. image:: _static/install-3.png

PhantomJS
===================

Printing of GroupedReports requires phantomjs to be installed on your server.



