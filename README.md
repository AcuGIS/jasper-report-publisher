# Jasper Report Publisher

[![Documentation Status](https://readthedocs.org/projects/jri-viewer/badge/?version=latest)](https://asper-report-publisher.docs.acugis.com/en/latest/?badge=latest)



## Publish, schedule, email, and run reports on demand

![QuartzMap](QuartzMap-Main.png)


## Install
Install on Ubuntu 22

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

Clone or download the repoistory::

    git clone https://github.com/AcuGIS/Jasper-Publisher
    mv Jasper-Publisher-master Jasper-Publisher

Navigate to /JasperPublisher and run the installers::

     ./installer/postgres.sh
     ./installer/app-install.sh
     ./installer/jri-install.sh
     ./installer/jri-sample.sh

Optionally, run below to provision SSL using letsencrypt::

    apt-get -y install python3-certbot-apache
    certbot --apache --agree-tos --email hostmaster@yourdomain.com --no-eff-email -d yourdomain.com


Navigate to https://yourdomain.com/admin/setup.php:

![Setup](docs/_static/install-1.png)

Enter the information for the PostgreSQL database you created:

![Setup](docs/_static/install-screen-2.png)

The installer will create the required objects in PostgreSQL

When the installer completes, you can log in using the email and password you selected above.

![Setup](docs/_static/install-3.png)

PhantomJS
===================

Printing of GroupedReports requires phantomjs to be installed on your server.




 
## Documentation

QuartMap Web Client [Documentation](https://quartzmap.docs.acugis.com).


## License
Version: MPL 2.0

The contents of this file are subject to the Mozilla Public License Version 2.0 (the "License"); you may not use this file except in compliance with the License. 

You may obtain a copy of the License at http://www.mozilla.org/MPL/
