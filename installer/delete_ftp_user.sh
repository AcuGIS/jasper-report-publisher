#!/bin/bash -e

FTP_USERNAME="${1}"

if [ $(groups "${FTP_USERNAME}" | grep -m 1 -c 'qatusers') -gt 0 ]; then
	userdel -r ${FTP_USERNAME}
fi