# Jasper Report Publisher

[![Documentation Status](https://readthedocs.org/projects/jri-viewer/badge/?version=latest)](https://jasper-report-publisher.docs.acugis.com/en/latest/?badge=latest)



## Publish, schedule, email, and run reports on demand

![Jasper Report Publisher](docs/_static/jasper-report-publisher-github.png)

## Overview

Jasper Publisher publishes, schedules, emails, and runs Jasper Reports on demand.

It supports PostgreSQL, Oracle, MySQL, and MSSQL and you can add additional data sources.

Jasper Report Publisher is free, Open Source software built around [JasperReportsIntegration](https://github.com/daust/JasperReportsIntegration)/). 

## Features

#### On Demand Reports for End Users with Group Level Permissions

![Jasper Report Publisher](docs/_static/Jasper-Report-Publisher-README.png)


#### Schedule, Email, and run Reports On Demand:

![Jasper Report Publisher](docs/_static/simple-bee-report.png)


#### Support for RPlotly and R Markdown Reports

![Jasper Report Publisher](docs/_static/R-animated.png)


#### Support for QGIS maps via QuartzMap

![Jasper Report Publisher](docs/_static/qgis-maps.png)


## Install
Install on Ubuntu 24

Installation is done via the install scripts located in the /installer directory.

System Requirements
=======================
* PostgreSQL 16 or 17
* PHP >= 8.1
* 4 GB RAM
* 15 GB Disk

Running the Installer
=======================

Download Latest Release::

    jasper-report-publisher-3.0.1.zip

Unzip and changed to jasper-report-publisher-3.0.1 directory

    Unzip -q jasper-report-publisher-3.0.1.zip
    cd jasper-report-publisher-3.0.1    

Run the installers::

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

### Note: 
If you installed the demo reports, be sure to restart Tomcat once logged in.

PhantomJS
===================

Printing of GroupedReports requires phantomjs to be installed on your server.




 
## Documentation

Jasper Report Publisher [Documentation](https://jasper-report-publisher.docs.acugis.com).


## License
Version: MPL 2.0

The contents of this file are subject to the Mozilla Public License Version 2.0 (the "License"); you may not use this file except in compliance with the License. 

You may obtain a copy of the License at http://www.mozilla.org/MPL/
