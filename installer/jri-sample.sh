#!/bin/bash -e

source /etc/environment

for ext in jasper jrxml png; do
	cp -v installer/sample/*.${ext} ${CATALINA_HOME}/jasper_reports/reports/
	chown tomcat:tomcat ${CATALINA_HOME}/jasper_reports/reports/*.${ext}
done