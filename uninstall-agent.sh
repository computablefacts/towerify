#!/bin/bash

systemctl stop osqueryd
systemctl stop logalert

apt-get purge --auto-remove osquery

rm -rf /opt/osquery
rm -rf /opt/logparser
rm -rf /opt/logalert
