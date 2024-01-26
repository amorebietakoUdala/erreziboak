#!/bin/bash

NETFOLDER=/var/www/SF6/erreziboak

sudo -u informatika -s `php $NETFOLDER/bin/console app:convert-daemon &>> $NETFOLDER/var/log/convert-daemon.log`
