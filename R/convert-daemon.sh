#!/bin/bash

NETFOLDER=/var/www/SF5/erreziboak

sudo -u informatika -s `php $NETFOLDER/bin/console app:convert-daemon &>> $NETFOLDER/var/log/convert-daemon.log`
