#!/bin/bash

systemctl stop osqueryd
systemctl stop logalert

crontab -l | grep -v "osquery" | crontab -
crontab -l | grep -v "logparser" | crontab -
crontab -l | grep -v "logalert" | crontab -
crontab -l | grep -v "cywise" | crontab -

apt-get purge --auto-remove osquery

rm -rf /opt/osquery
rm -rf /opt/logparser
rm -rf /opt/logalert
