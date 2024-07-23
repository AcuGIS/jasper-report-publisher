.. This is a comment. Note how any initial comments are moved by
   transforms to after the document title, subtitle, and docinfo.

.. demo.rst from: http://docutils.sourceforge.net/docs/user/rst/demo.txt

.. |EXAMPLE| image:: static/yi_jing_01_chien.jpg
   :width: 1em

**********************
Sample Data
**********************

.. contents:: Table of Contents
Overview
==================

If you select the "Load Sample Data" box, it will create a sample database and reports.
  
Sample Database
================

The sample database, beedatabase, is taken from the QFieldCloud Simple Bee Project::

  beedatabase=# \dt
               List of relations
   Schema |      Name       | Type  | Owner
  --------+-----------------+-------+--------
   public | apiary          | table | admin1
   public | fields          | table | admin1
   public | spatial_ref_sys | table | jrv
  (3 rows)

Sample Reports
================

Three Sample Reports are created

* Simple Bee Report	- this is a basic chart report

.. image:: ../../_static/simple-bee-report.png


* LOV Parameter - This is a basic report using a single LOV (List of Values) Parameter

.. image:: ../../_static/lov-report-0.png


* Query Parameter - This is a basic report using two Query Parameters

.. image:: ../../_static/query-report-3.png

* File Name	- jrxml name of the report
* Name	- the name that will appear in the dashboard
* Description	- the description that will appear in the dashboard
* Access Groups - Group(s) that have access to this report
* Grouped Report - Report is part of Grouped Report   


With all fields populated, click the Add icon at right:

.. image:: ../../_static/report-dashboard-1.png


Report Thumbnail
=====================

You can set the thumbnail for a report by adding reportid.png to the assets/maps folder.

Edit Report
===================
To edit a report entry, click the Edit icon, as shown below:

.. image:: _static/Edit-Report.png

Delete Report
===================
To delete a report entry, click the Delete icon, as shown below:

.. image:: _static/Edit-Report.png

