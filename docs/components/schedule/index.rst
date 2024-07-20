**********************
Schedule
**********************

.. contents:: Table of Contents
Scheduling Reports
==================

Click on Schedule on the left menu.

Click the Add Schedule button

.. image:: ../../_static/schedule-1.png

The top section includes required fields:

.. image:: ../../_static/schedule-2.png

The bottom section includes option fields for email reports

.. image:: ../../_static/schedule-3.png

Scheduling Options
==================

The Schedule module offers the following options.

**Execute**::

   Options:
      now
      custom
      hourly
      weekly
      monthly
      
now:  This will run the report immediately, with no subsequent runs.

custom: This option allows you to enter a custom cron for running the report
 
hourly, weekly, and monthly are as stated.
 
**Name**::

   Options:
      Drop-down list of all available reports


The Name field will display a list of all available reports.  Above, we have select the NewReports/ClassReports we created earlier.


**Format**::

   Options:
      csv
      docx
      html
      html2
      jxl
      pdf
      pptx
      rtf
      xls
      xlsx

Select the desired output format for the report.


**Data Source**::

   Options:
      Displays a drop-down list of Data Sources you have created.

Select the desired Data Source for the report.

**File Name**::

   Options:
      Enter the desired file name WITH Extension.
      Example: ClassReports.pdf

Enter the desired Data Source for the report.


**Email**::

   Options:
      Enter email address or comma separated list of addresses for delivery.

Enter the desired Data Source for the report.

.. note::
    If you do not wish to email the report, tick the "Don't send email" box.  
    This will run the report and save it to disk on the server.
    The report can be retrived via disk or downloaded via Reports tab.



Optional Params
===============

The Optional Params tab allows you to:

1. Set email subject
2. Set email message
3. Add report parameters


URL Parameters
===============

To add a Report Parameter to the report URL, enter the variable in the left box and the value in the right box as shown below:

.. image:: _static/schedule-params.png


Click the Save button.

.. image:: _static/schedule-optional-params.png

You can add as many parameters as you wish to.

Finally, click the Creat button to schedule the report.

Additional Examples
===================

Below are some additional examples.

**Send report every Tuesday at 1500 (3 PM)**

.. image:: _static/schedule-tuesdays.png

**Send report every hour without email delivery**

.. image:: _static/schedule-hour.png

**Send report weekly with StudentID = 51**

.. image:: _static/schedule-weekly.png

   





