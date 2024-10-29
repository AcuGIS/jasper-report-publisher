#!/bin/bash -e

read FTP_USERNAME
read FTP_PASSWORD

if [ $(groups "${FTP_USERNAME}" | grep -m 1 -c 'qatusers') -gt 0 ]; then	
	echo -n "${FTP_USERNAME}:${FTP_PASSWORD}" | chpasswd -e
fi