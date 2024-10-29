#!/bin/bash -e

TOMCAT_MAJOR=9

function install_tomcat(){

	apt-get -y install haveged logrotate libtcnative-1

	if [ ! -d /home/tomcat ]; then
		useradd -m tomcat
	fi
	
	pushd /home/tomcat

	if [ ! -d apache-tomcat-${TOMCAT_VER} ]; then
		if [ ! -f /tmp/apache-tomcat-${TOMCAT_VER}.tar.gz ]; then
			wget -q -P/tmp "https://archive.apache.org/dist/tomcat/tomcat-${TOMCAT_MAJOR}/v${TOMCAT_VER}/bin/apache-tomcat-${TOMCAT_VER}.tar.gz"
		fi
		tar xzf /tmp/apache-tomcat-${TOMCAT_VER}.tar.gz
		chown -R tomcat:tomcat apache-tomcat-${TOMCAT_VER}
		rm -rf /tmp/apache-tomcat-${TOMCAT_VER}.tar.gz
	fi
	popd

	if [ $(grep -m 1 -c CATALINA_HOME /etc/environment) -eq 0 ]; then
		cat >>/etc/environment <<EOF
CATALINA_HOME=/home/tomcat/apache-tomcat-${TOMCAT_VER}
CATALINA_BASE=/home/tomcat/apache-tomcat-${TOMCAT_VER}
EOF
	fi

	TOMCAT_MANAGER_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c32);
	TOMCAT_ADMIN_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c32);

	if [ $(grep -m 1 -c 'tomcat manager pass' /root/auth.txt) -eq 0 ]; then
		echo "tomcat manager pass: ${TOMCAT_MANAGER_PASS}" >> /root/auth.txt
	else
		sed -i.save "s/tomcat manager pass: .*/tomcat manager pass: ${TOMCAT_MANAGER_PASS}/" /root/auth.txt
	fi

	if [ $(grep -m 1 -c 'tomcat admin pass' /root/auth.txt) -eq 0 ]; then
		echo "tomcat admin pass: ${TOMCAT_ADMIN_PASS}" >> /root/auth.txt
	else
		sed -i.save "s/tomcat admin pass: .*/tomcat admin pass: ${TOMCAT_ADMIN_PASS}/" /root/auth.txt
	fi

	cat >/home/tomcat/apache-tomcat-${TOMCAT_VER}/conf/tomcat-users.xml <<EOF
<?xml version='1.0' encoding='utf-8'?>
<tomcat-users>
<role rolename="manager-gui" />
<user username="manager" password="${TOMCAT_MANAGER_PASS}" roles="manager-gui" />

<role rolename="admin-gui" />
<user username="admin" password="${TOMCAT_ADMIN_PASS}" roles="manager-gui,admin-gui" />
</tomcat-users>
EOF

	#folder is created after tomcat is started, but we need it now
	mkdir -p /home/tomcat/apache-tomcat-${TOMCAT_VER}/conf/Catalina/localhost/
	cat >/home/tomcat/apache-tomcat-${TOMCAT_VER}/conf/Catalina/localhost/manager.xml <<EOF
<Context privileged="true" antiResourceLocking="false" docBase="\${catalina.home}/webapps/manager">
	<Valve className="org.apache.catalina.valves.RemoteAddrValve" allow="^.*\$" />
</Context>
EOF

	chown -R tomcat:tomcat /home/tomcat

	cat >>"${CATALINA_HOME}/bin/setenv.sh" <<CMD_EOF
CATALINA_PID="${CATALINA_HOME}/temp/tomcat.pid"
JAVA_OPTS="\${JAVA_OPTS} -server -Djava.awt.headless=true -Dorg.geotools.shapefile.datetime=false -XX:+UseParallelGC -XX:ParallelGCThreads=4 -Dfile.encoding=UTF8 -Duser.timezone=UTC -Djavax.servlet.request.encoding=UTF-8 -Djavax.servlet.response.encoding=UTF-8 -DGEOSERVER_CSRF_DISABLED=true -DPRINT_BASE_URL=http://localhost:8080/geoserver/pdf -Dgwc.context.suffix=gwc"
CMD_EOF

	cat >/etc/systemd/system/tomcat.service <<EOF
[Unit]
Description=Tomcat $TOMCAT_VER
After=multi-user.target

[Service]
User=tomcat
Group=tomcat

WorkingDirectory=$CATALINA_HOME
Type=forking
Restart=always

EnvironmentFile=/etc/environment

ExecStart=$CATALINA_HOME/bin/startup.sh
ExecStop=$CATALINA_HOME/bin/shutdown.sh 60 -force

[Install]
WantedBy=multi-user.target
EOF

	cat >/etc/logrotate.d/tomcat <<CAT_EOF
${CATALINA_HOME}/logs/catalina.out {
	copytruncate
	daily
	rotate 7
	compress
	missingok
	size 15M
}
CAT_EOF

	chmod +x /etc/systemd/system/tomcat.service
	systemctl daemon-reload

	systemctl enable tomcat
	systemctl start tomcat
}

function install_java(){
	apt-get -y install openjdk-8-jdk openjdk-8-jre-headless
}

function install_jri(){
  mv installer/gen_jri_report.sh /usr/local/bin/
  chmod +x /usr/local/bin/gen_jri_report.sh
	
	# don't store sent emails
	echo 'set copy = no' > /var/www/.muttrc
	chown www-data:www-data /var/www/.muttrc
	
	# create empty schedule files, to avoid errors
	touch ${JASPER_HOME}/jri_schedule.crontab
	chown www-data:www-data ${JASPER_HOME}/jri_schedule.crontab
	
	for p in hourly daily weekly monthly; do
		echo '#!/bin/bash -e' >> /etc/cron.${p}/jri_schedules.sh
		chown www-data:www-data /etc/cron.${p}/jri_schedules.sh
		chmod +x /etc/cron.${p}/jri_schedules.sh
	done
}

function install_jri_war(){

  JASPER_HOME="${CATALINA_HOME}/jasper_reports"
	mkdir -p "${JASPER_HOME}"
	chmod -R g+w ${JASPER_HOME}

	JRI_LATEST_TAG=$(wget -q -O- https://github.com/daust/JasperReportsIntegration/releases/latest | sed -n 's|.*/daust/JasperReportsIntegration/releases/tag/\(v[0-9\.]\+\).*|\1|p' | head -n 1)
  JRI_URL_PATH=$(wget -q -O- https://github.com/daust/JasperReportsIntegration/releases/expanded_assets/${JRI_LATEST_TAG} | sed -n 's|.*\(/daust/JasperReportsIntegration/releases/download/.*\.zip\).*|\1|p')

  wget --no-check-certificate -P/tmp "https://github.com/${JRI_URL_PATH}"
  JRI_ARCHIVE=$(basename ${JRI_URL_PATH})

  unzip /tmp/${JRI_ARCHIVE}
  rm -f /tmp/${JRI_ARCHIVE}

  JRI_FOLDER=$(echo ${JRI_ARCHIVE} | sed 's/.zip//')
  mv ${JRI_FOLDER}/webapp/jri.war ${CATALINA_HOME}/webapps/JasperReportsIntegration.war

  for d in reports conf logs schedules; do
    if [ -d ${JRI_FOLDER}/${d} ]; then
      mv ${JRI_FOLDER}/${d} ${JASPER_HOME}/${d}
    else
      mkdir ${JASPER_HOME}/${d}
    fi
  done

  #run jri script setConfigDir.sh
  pushd ${JRI_FOLDER}/bin
    chmod +x encryptPasswords.sh
    if [ -f setConfigDir.sh ]; then
      chmod +x setConfigDir.sh
      ./setConfigDir.sh ${CATALINA_HOME}/webapps/JasperReportsIntegration.war ${JASPER_HOME}
    fi
  popd
  rm -rf ${JRI_FOLDER}

  chown -R tomcat:tomcat ${JASPER_HOME}

  echo "OC_JASPER_CONFIG_HOME=\"${JASPER_HOME}\"" >> ${CATALINA_HOME}/bin/setenv.sh
	sed -i.save "s|^[# ]*reportsPath=.*|reportsPath=${JASPER_HOME}/reports|" ${JASPER_HOME}/conf/application.properties
	
  systemctl restart tomcat
}

function install_jri_pg(){
  JRI_PG_VER=$(wget -O- https://jdbc.postgresql.org/download/ | sed -n 's/.*href="\/download\/postgresql\-\([0-9\.]\+\)\.jar.*/\1/p' | head -n 1)

  wget --no-check-certificate -P/tmp "https://jdbc.postgresql.org/download/postgresql-${JRI_PG_VER}.jar"
  mv /tmp/postgresql-${JRI_PG_VER}.jar ${CATALINA_HOME}/lib/

  sed -i.save '/^<\/Context>/d' ${CATALINA_HOME}/conf/context.xml

  cat >>${CATALINA_HOME}/conf/context.xml <<CMD_EOF
<Resource name="jdbc/postgres" auth="Container" type="javax.sql.DataSource"
  driverClassName="org.postgresql.Driver"
  maxTotal="20" initialSize="0" minIdle="0" maxIdle="8"
  maxWaitMillis="10000" timeBetweenEvictionRunsMillis="30000"
  minEvictableIdleTimeMillis="60000" testWhileIdle="true"
  validationQuery="select user" maxAge="600000"
  rollbackOnReturn="true"
  url="jdbc:postgresql://localhost:5432/xxx"
  username="xxx"
  password="xxx"
/>
</Context>
CMD_EOF


  sed -i.save '/^<\/web-app>/d' ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml

  cat >>${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml <<CMD_EOF
<resource-ref>
  <description>postgreSQL Datasource example</description>
  <res-ref-name>jdbc/postgres</res-ref-name>
  <res-type>javax.sql.DataSource</res-type>
  <res-auth>Container</res-auth>
</resource-ref>
</web-app>
CMD_EOF
}

function install_jri_mysql(){
  JRI_MYSQL_VER=$(wget -O- https://dev.mysql.com/downloads/connector/j/ | sed -n 's/.*<h1>Connector\/J[ \t]\+\([0-9\.]\+\)[ \t].*/\1/p')

  wget --no-check-certificate -P/tmp "https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-j-${JRI_MYSQL_VER}.zip"
  pushd /tmp/
    unzip /tmp/mysql-connector-j-${JRI_MYSQL_VER}.zip
    mv mysql-connector-j-${JRI_MYSQL_VER}/mysql-connector-j-${JRI_MYSQL_VER}.jar ${CATALINA_HOME}/lib/
    rm -rf mysql-connector-j-${JRI_MYSQL_VER}/
  popd

  sed -i.save '/^<\/Context>/d' ${CATALINA_HOME}/conf/context.xml
  cat >>${CATALINA_HOME}/conf/context.xml <<CMD_EOF
<Resource name="jdbc/MySQL" auth="Container" type="javax.sql.DataSource"
maxTotal="100" maxIdle="30" maxWaitMillis="10000"
driverClassName="com.mysql.jdbc.Driver"
username="xxx" password="xxx"  url="jdbc:mysql://localhost:3306/xxx"/>
</Context>
CMD_EOF

  sed -i.save '/^<\/web-app>/d' ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml
  cat >>${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml <<CMD_EOF
<resource-ref>
<description>MySQL Datasource example</description>
<res-ref-name>jdbc/MySQL</res-ref-name>
<res-type>javax.sql.DataSource</res-type>
<res-auth>Container</res-auth>
</resource-ref>
</web-app>
CMD_EOF
}

function install_jri_mssql(){
  wget -O/tmp/mssql.html 'https://docs.microsoft.com/en-us/sql/connect/jdbc/download-microsoft-jdbc-driver-for-sql-server?view=sql-server-ver15'

  JRI_MSSQL_URL=$(grep 'Download Microsoft JDBC Driver' /tmp/mssql.html | grep 'SQL Server (zip)' | grep 'linkid=' | cut -f2 -d'"')
  JRI_MSSQL_VER=$(grep -m 1 "${JRI_MSSQL_URL}" /tmp/mssql.html | sed -n 's/.*Download Microsoft JDBC Driver \([0-9\.]\+\) for SQL Server (zip).*/\1/p')
  wget -O/tmp/mssql.zip "${JRI_MSSQL_URL}"

  mkdir -p temp
  pushd temp
    unzip /tmp/mssql.zip
    find "sqljdbc_${JRI_MSSQL_VER}/enu/" -type f -name "mssql-jdbc-*.jre8.jar" -exec mv {} ${CATALINA_HOME}/lib/ \;
  popd
  rm -r temp /tmp/mssql.zip

  sed -i.save '/^<\/Context>/d' ${CATALINA_HOME}/conf/context.xml
  cat >>${CATALINA_HOME}/conf/context.xml <<CMD_EOF
<Resource name="jdbc/MSSQL" auth="Container" type="javax.sql.DataSource"
maxTotal="100" maxIdle="30" maxWaitMillis="10000"
driverClassName="com.microsoft.sqlserver.jdbc.SQLServerDriver"
username="xxx" password="xxx"  url="jdbc:sqlserver://localhost:1433;databaseName=xxx"/>
</Context>
CMD_EOF


sed -i.save '/^<\/web-app>/d' ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml
cat >>${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml <<CMD_EOF
<resource-ref>
<description>MSSQL Datasource example</description>
<res-ref-name>jdbc/MSSQL</res-ref-name>
<res-type>javax.sql.DataSource</res-type>
<res-auth>Container</res-auth>
</resource-ref>
</web-app>
CMD_EOF
}

function install_email_template(){
  mkdir ${JASPER_HOME}/email_tmpl
  mv installer/email_template.html "${JASPER_HOME}/email_tmpl/"
  chown -R tomcat:tomcat "${JASPER_HOME}/email_tmpl"
}

function setup_webapp_proxy(){
	apt-get install -y apache2

	cat >/etc/apache2/conf-available/tomcat.conf <<CMD_EOF
ProxyRequests Off
ProxyPreserveHost On
<Proxy *>
	Order allow,deny
	Allow from all
</Proxy>
ProxyPass				 / http://localhost:8080/
ProxyPassReverse / http://localhost:8080/
CMD_EOF
	a2enmod proxy proxy_http rewrite
}

function install_jri_deps(){
	apt-get install -y mutt zip postfix cron unzip
}

function app_fixes(){
	# allow edits from web server
	chmod -R g+rwx ${CATALINA_HOME}/conf/
	chmod -R g+rw ${CATALINA_HOME}/jasper_reports
	chmod g+rw ${CATALINA_HOME}/conf/context.xml

	while [ ! -f ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml ]; do
		echo "Waiting for ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml"
		sleep 1;
	done

	chmod g+rw ${CATALINA_HOME}/webapps/JasperReportsIntegration/WEB-INF/web.xml
	
	# allow apache to access tomcat Jasper files
	usermod -a -G tomcat www-data
	
	cat >/etc/apache2/conf-available/jri.conf <<CAT_EOF
Alias /reports/ ${CATALINA_HOME}/jasper_home/reports
<Directory "${CATALINA_HOME}/jasper_home/reports">
  AllowOverride None
  Options -Indexes -MultiViews
  Require all granted
</Directory>

RewriteEngine on
RewriteRule ^/report_image /report_image.php [L,QSA]
CAT_EOF
	
	sed -i.save '/<\/VirtualHost>/iInclude /etc/apache2/conf-available/jri.conf' /etc/apache2/sites-available/default-ssl.conf
	sed -i.save '/<\/VirtualHost>/iInclude /etc/apache2/conf-available/jri.conf' /etc/apache2/sites-available/000-default.conf
	if [ -f /etc/apache2/sites-available/000-default-le-ssl.conf ]; then
		sed -i.save '/<\/VirtualHost>/iInclude /etc/apache2/conf-available/jri.conf' /etc/apache2/sites-available/000-default-le-ssl.conf
	fi
	
	PHP_VER=$(php -version | head -n 1 | cut -f2 -d' ' | cut -f1,2 -d.)
	systemctl restart apache2 php${PHP_VER}-fpm
	
	# add rmaps user for cron updates
	useradd -s /usr/sbin/nologin -M -d /var/www -G www-data,tomcat rmaps
}

touch /root/auth.txt

export DEBIAN_FRONTEND=noninteractive
apt-add-repository -y universe

apt-get -y install wget unzip tar

TOMCAT_VER=$(wget -q -O- --no-check-certificate https://archive.apache.org/dist/tomcat/tomcat-${TOMCAT_MAJOR}/ | sed -n "s|.*<a href=\"v\(${TOMCAT_MAJOR}\.[0-9.]\+\)/\">v.*|\1|p" | sort -V | tail -n 1)
if [ -z "${TOMCAT_VER}" ]; then
	echo "Error: Failed to get tomcat version"; exit 1;
fi
CATALINA_HOME="/home/tomcat/apache-tomcat-${TOMCAT_VER}"

setup_webapp_proxy;
install_java;
install_tomcat;
install_jri_war;  #needs to be here, since it takes time to deploy
install_jri_deps;
install_jri;
#jri webapp must be deployed yet
install_jri_pg;
install_jri_mysql;
install_jri_mssql;

install_email_template;

app_fixes;

echo "Passwords saved in /root/auth.txt"
cat /root/auth.txt
