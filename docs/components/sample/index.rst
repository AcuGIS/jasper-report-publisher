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

**IMPORTANT** : If you load the Sample Data, you will need to restart Tomcat in order to pick up sample data.

.. image:: ../../_static/tomcat-restart.png


Dashboard
================

The sample reports are available on the Dashboard

.. image:: ../../_static/sample-dashboard.png
  
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


Sample Data Source
================

The included sample Data Source is a JNDI connection to the beedatabase:

.. image:: ../../_static/sample-data-source.png



Sample Reports
================

Three Sample Reports are created

* Simple Bee Report	- this is a basic chart report

.. image:: ../../_static/simple-bee-report.png


* LOV Parameter - This is a basic report using a single LOV (List of Values) Parameter

.. image:: ../../_static/lov-report-0.png


* Query Parameter - This is a basic report using two Query Parameters

.. image:: ../../_static/query-report-3.png


Sample Schedules
================

A sample Schedule is created for each report.

Note: These Schedules, do not have email activated.  You can edit them to include email delivery to test email functionality.

.. image:: ../../_static/sample-schedule.png



Sample Parameters
=====================

Sample Parameters are include for the LOV Parameter and Query Parameter reports

.. image:: ../../_static/sample-parameter.png

Delete Sample Data
===================

To delete the sample data:

1. Delete Sample Schedules
2. Delete Sample Reports
3. Delete Sample Data Sources
4. Drop beedatabase



