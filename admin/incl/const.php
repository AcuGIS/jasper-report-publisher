<?php
define("DB_HOST", "localhost");
define("DB_NAME", "jrv");
define("DB_USER", "jrv");
define("DB_PASS", "tRIYq1DW1jIktEk26SCWTIaRWkC7tQMo");
define("DB_PORT", 5432);
define("DB_SCMA", 'public');
define("APPS_DIR", "/var/www/html/apps");
define("CACHE_DIR", "/var/www/cache");
define("DATA_DIR", "/var/www/data");
const ACCESS_LEVELS = array('User', 'Admin', 'Devel');
const ADMINISTRATION_ACCESS = array('Admin', 'Devel');
define("SESS_USR_KEY", 'jri_user');
define("SUPER_ADMIN_ID", 1);
const CRON_PERIOD = array('never', 'hourly', 'daily', 'weekly', 'monthly', 'custom');
?>
