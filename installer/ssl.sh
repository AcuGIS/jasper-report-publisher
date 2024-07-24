#!/bin/bash -e

HNAME=$(hostname -f)

function install_certbot() {
apt-get -y install python3-certbot-apache
service apache2 restart
sleep 5;

}

function request_cert() {
	certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
	sleep 12;
}


function get_repo(){
	if [ -f /etc/rocky-release ]; then
		REPO='rpm'
		
	elif [ -f /etc/debian_version ]; then
		REPO='apt'
	fi
}

function webmin_ssl() {
	cat /etc/letsencrypt/live/${HNAME}/cert.pem > /etc/webmin/miniserv.pem
	cat /etc/letsencrypt/live/${HNAME}/privkey.pem >> /etc/webmin/miniserv.pem
	echo "extracas=/etc/letsencrypt/live/${HNAME}/fullchain.pem" >> /etc/webmin/miniserv.conf
	
	systemctl restart webmin
}

function restart_apache() {
	if [ "${REPO}" == 'apt' ]; then
		systemctl restart apache2
	elif [ "${REPO}" == 'rpm' ]; then
		systemctl restart httpd
	fi
}


install_certbot;
request_cert;
webmin_ssl;
restart_apache;

