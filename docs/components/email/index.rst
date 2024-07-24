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

The directory for storing HTML templates for email is::

     /home/tomcat/apache-tomcat-version/jasper_reports/email_tmpl 

This directory contains a started template you can customize (email_template.html).

You can also add your own templates to this directory as well.

Any templates in this directory will appear in the dropdown box on the Report Schedule page;

.. image:: ../../_static/schedule-4.png

Using the included email_template.html, the Report email will look like below:

.. image:: ../../_static/email-templates.png

PostFix
=====================

By default, Jasper Report Publisher installs PostFix as MTA

This can be subsituted with Sendmail or any other MTA you wish to use.

MUTT
===================
Jasper Publisher uses MUTT email client.



