#!/bin/bash -e

APP_DB='jrv'
APP_DB_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);
DATA_DIR='/var/www/data'
CACHE_DIR='/var/www/cache'
APPS_DIR='/var/www/html/apps'

HNAME=$(hostname -f)

function install_qgis_server(){

	RELEASE=$(lsb_release -cs)
	wget --no-check-certificate --quiet -O /etc/apt/keyrings/qgis-archive-keyring.gpg https://download.qgis.org/downloads/qgis-archive-keyring.gpg

	# 3.28.x Firenze 				​-> URIs: https://qgis.org/ubuntu
	# 3.22.x Białowieża LTR	-> URIs: https://qgis.org/ubuntu-ltr
	cat >>/etc/apt/sources.list.d/qgis.sources <<CAT_EOF
Types: deb deb-src
URIs: https://qgis.org/ubuntu
Suites: ${RELEASE}
Architectures: amd64
Components: main
Signed-By: /etc/apt/keyrings/qgis-archive-keyring.gpg
CAT_EOF

	apt-get update -y || true
  apt-get install -y qgis-server
	
	if [ -d /etc/logrotate.d ]; then
		cat >/etc/logrotate.d/qgisserver <<CAT_EOF
/var/log/qgisserver.log {
	su www-data www-data
	size 100M
	notifempty
	missingok
	rotate 3
	daily
	compress
	create 660 www-data www-data
}
CAT_EOF
	fi
	
	mkdir -p ${DATA_DIR}/qgis
	chown www-data:www-data ${DATA_DIR}/qgis
	
	touch /var/log/qgisserver.log
	chown www-data:www-data /var/log/qgisserver.log
}

touch /root/auth.txt
export DEBIAN_FRONTEND=noninteractive

if [ ! -f /usr/bin/createdb ]; then
	echo "Error: Missing PG createdb! First run ./installer/postgres.sh"; exit 1;
fi

if [ ! -d installer ]; then
	echo "Usage: ./installer/app-installer.sh"
	exit 1
fi

# 1. Install packages (assume PG is preinstalled)
apt-get -y install apache2 php-{pgsql,zip,gd,simplexml,curl,fpm} \
	proftpd libapache2-mod-fcgid postfix python3-certbot-apache gdal-bin \
	r-base r-base-dev r-cran-{raster,htmlwidgets,plotly,rnaturalearthdata,rjson,skimr}  \
	texlive-latex-base texlive-latex-recommended texlive-xetex cron

apt-get -y install --no-install-suggests --no-install-recommends texlive-latex-extra

	
	install_qgis_server

# compile leaflet package from CRAN
R --no-save <<R_EOF
install.packages( c('leaflet', 'leaflet.extras', 'rpostgis', 'R3port', 'rnaturalearth'))
R_EOF

# setup apache
a2enmod ssl headers expires fcgid cgi
cp installer/apache2.conf /etc/apache2/sites-available/default-ssl.conf

sed "s|\$DATA_DIR|$DATA_DIR|" < installer/qgis_apache2.conf > /etc/apache2/sites-available/qgis.conf

for f in default-ssl 000-default; do
	sed -i.save "s/#ServerName example.com/ServerName ${HNAME}/" /etc/apache2/sites-available/${f}.conf
done

a2ensite 000-default default-ssl qgis
a2disconf serve-cgi-bin

# switch to mpm_event to server faster and use HTTP2
PHP_VER=$(php -version | head -n 1 | cut -f2 -d' ' | cut -f1,2 -d.)
a2enmod proxy_fcgi setenvif http2
a2enconf php${PHP_VER}-fpm
a2enmod mpm_event

systemctl reload apache2

#certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}

sed -i.save '
s/#DefaultRoot~/DefaultRoot ~/
s/# RequireValidShell\s*off/RequireValidShell off/' /etc/proftpd/proftpd.conf
systemctl enable proftpd
systemctl restart proftpd

# 2. Create db
su postgres <<CMD_EOF
createdb ${APP_DB}
createuser -sd ${APP_DB}
psql -c "alter user ${APP_DB} with password '${APP_DB_PASS}'"
psql -c "ALTER DATABASE ${APP_DB} OWNER TO ${APP_DB}"
CMD_EOF

echo "${APP_DB} pass: ${APP_DB_PASS}" >> /root/auth.txt

mkdir -p "${APPS_DIR}"
mkdir -p "${APPS_DIR}/js"
mkdir -p "${APPS_DIR}/css"
mkdir -p "${CACHE_DIR}"
mkdir -p "${DATA_DIR}/qgis"

chown -R www-data:www-data "${APPS_DIR}"
chown -R www-data:www-data "${CACHE_DIR}"
chown -R www-data:www-data "${DATA_DIR}"

# sync service needs +w to apps/1/images dir
chmod -R g+w "${APPS_DIR}"

cat >admin/incl/const.php <<CAT_EOF
<?php
define("DB_HOST", "localhost");
define("DB_NAME", "${APP_DB}");
define("DB_USER", "${APP_DB}");
define("DB_PASS", "${APP_DB_PASS}");
define("DB_PORT", 5432);
define("DB_SCMA", 'public');
define("APPS_DIR", "${APPS_DIR}");
define("CACHE_DIR", "${CACHE_DIR}");
define("DATA_DIR", "${DATA_DIR}");
const ACCESS_LEVELS = array('User', 'Admin', 'Devel');
const ADMINISTRATION_ACCESS = array('Admin', 'Devel');
define("SESS_USR_KEY", 'jri_user');
define("SUPER_ADMIN_ID", 1);
const CRON_PERIOD = array('never', 'hourly', 'daily', 'weekly', 'monthly', 'custom');
?>
CAT_EOF

cp -r . /var/www/html/
chown -R www-data:www-data /var/www/html
rm -rf /var/www/html/installer

systemctl restart apache2

# create group for all FTP users
groupadd qatusers

# install ftp user creation script
for f in create_ftp_user delete_ftp_user update_ftp_user svc_ctl chown_ctl rmaps_crontab; do
	cp installer/${f}.sh /usr/local/bin/
	chown www-data:www-data /usr/local/bin/${f}.sh
	chmod 0550 /usr/local/bin/${f}.sh
done

cat >/etc/sudoers.d/jri <<CAT_EOF
www-data ALL = NOPASSWD: /usr/local/bin/create_ftp_user.sh, /usr/local/bin/delete_ftp_user.sh, /usr/local/bin/update_ftp_user.sh, /usr/local/bin/svc_ctl.sh, /usr/local/bin/chown_ctl.sh, /usr/local/bin/rmaps_crontab.sh
CAT_EOF

# create empty cron file
touch "$DATA_DIR/rmaps.crontab"
chown www-data:www-data "$DATA_DIR/rmaps.crontab"

# save 1Gb of space
apt-get -y clean all
