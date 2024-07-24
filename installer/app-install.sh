#!/bin/bash -e

APP_DB='jrv'
APP_DB_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);

# 1. Install packages (assume PG is preinstalled)
apt-get -y install apache2 libapache2-mod-php php-{pgsql,yaml,simplexml,curl}

# 2. Create db
su postgres <<CMD_EOF
createdb ${APP_DB}
createuser -sd ${APP_DB}
psql -c "alter user ${APP_DB} with password '${APP_DB_PASS}'"
psql -c "ALTER DATABASE ${APP_DB} OWNER TO ${APP_DB}"
CMD_EOF

echo "${APP_DB} pass: ${APP_DB_PASS}" >> /root/auth.txt

cat >admin/incl/const.php <<CAT_EOF
<?php
define("DB_HOST", "localhost");
define("DB_NAME", "${APP_DB}");
define("DB_USER", "${APP_DB}");
define("DB_PASS", "${APP_DB_PASS}");
define("DB_PORT", 5432);
define("DB_SCMA", 'public');
const ACCESS_LEVELS = array('User', 'Admin', 'Devel');
const ADMINISTRATION_ACCESS = array('Admin', 'Devel');
define("SESS_USR_KEY", 'jri_user');
define("SUPER_ADMIN_ID", 1);
?>
CAT_EOF

# install ftp user creation script
for f in svc_ctl chown_ctl; do
	cp installer/${f}.sh /usr/local/bin/
	chown www-data:www-data /usr/local/bin/${f}.sh
	chmod 0550 /usr/local/bin/${f}.sh
done

cat >/etc/sudoers.d/jri <<CAT_EOF
www-data ALL = NOPASSWD: /usr/local/bin/svc_ctl.sh, /usr/local/bin/chown_ctl.sh
CAT_EOF

cp -r . /var/www/html/
chown -R www-data:www-data /var/www/html

systemctl restart apache2
