.. This is a comment. Note how any initial comments are moved by
   transforms to after the document title, subtitle, and docinfo.

.. demo.rst from: http://docutils.sourceforge.net/docs/user/rst/demo.txt

.. |EXAMPLE| image:: static/yi_jing_01_chien.jpg
   :width: 1em

**********************
Security
**********************

Like JasperReportsIntegration, JRI Viewer is designed to be used behind a firewall.

You should enable your firewall as well.


Disable Info Page
=========================

The JRI Info page should be disabled

Line 31 of application.properties should be changed from::

   infoPageIsEnabled=true

to::

   infoPageIsEnabled=false

Demo Directory
=========================

Remove the /demo directory::

   rm -rf /home/tomcat/tomcat-VERSION/jasper_reports/reports/demo

Encrypt Passwords
=========================

Encrypt Password in application.properties file::

   sub encrypt_prop_passwords(){
   my $web_inf = get_catalina_home().'/webapps/JasperReportsIntegration/WEB-INF';
   my $cmd = "cd $web_inf/classes; java -cp '.:../lib/*' main/CommandLine encryptPasswords ".get_jasper_home()."/conf/application.properties";
   exec_cmd($cmd);

Set Allowed IPs
=========================

Set the allowed IPs::

   # ipAddressesAllowed=127.0.0.1,10.10.10.10,192.168.178.31
   # if the list is empty, ALL addresses are allowed


  
