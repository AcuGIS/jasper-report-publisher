**********************
SFTP Plugin
**********************

.. contents:: Table of Contents
Installation
==================

Download acugis_sftp_tool.zip to your desktop

In QGIS, go to Plugins > Manage and Install Plugins and click on "Install from Zip" in left menu.

Browse to location where you saved acugis_sftp_tool.zip and then click "Install Plugin"

.. image:: install-1.png

Once installed, you should see the Plugins menu.

Usage
==================
  
Begin by selecting Configure SFTP servers:  

.. image:: plugin-1.png

Click Add to add server(s).

.. image:: ConfigureSFTPServers.png   

Click Save.

Go to Upload Project Directory via SFTP

.. image:: sftp.png

Select the Server you wish to upload to.  

Use the "Browse Remote Path" button to browse the remote directories, or simply enter the remote location (file path) to upload to (e.g. /var/www/html)

If your owner is a user:group other than wwww-data, change it in the Owbership field.

.. warning::
    The entire QGIS Project directory will be uploaded.

Click Upload

.. image:: UploadQGISProject.png  

.. note::
    If files exist, you will prompted if you wish to overwrite files.
    
A success message will be displayed up completion.

Use Cases
==================

The plugin is generic and can be used for SFTP'ing a QGIS Project directory to any remote location via SFTP.

Some specific use cases are:

- Liamap: While it is still neccessary to create a Repository on disk and register it via Lizmap admin, once you have done so the plugin can transfer the project as well as update files as needed.

- QWC2: Can be used to upload to /scan directory.  Once uploaded, Configuration Utility must still be run for the project.




