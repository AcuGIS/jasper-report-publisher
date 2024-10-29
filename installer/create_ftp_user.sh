#!/bin/bash -e

read FTP_USERNAME
read FTP_PASSWORD

useradd -G qatusers -m -s /usr/sbin/nologin ${FTP_USERNAME}
echo -n "${FTP_USERNAME}:${FTP_PASSWORD}" | chpasswd -e

FTP_HOME=$(grep "^${FTP_USERNAME}:" /etc/passwd | cut -f6 -d:)

# allow Apache access
chown ${FTP_USERNAME}:www-data "${FTP_HOME}"
chmod 0755 "${FTP_HOME}"

