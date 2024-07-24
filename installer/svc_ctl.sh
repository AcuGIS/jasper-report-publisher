#!/bin/bash -e

SVC=$1
OP=$2

if [ "${SVC}" == 'tomcat' ]; then
	systemctl ${OP} ${SVC}.service
fi