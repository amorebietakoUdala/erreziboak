#!/bin/bash

iconv -f=Windows-1252 -t=UTF-8 $1 > $1.utf8
Rscript --default-packages="stringr,dplyr,lubridate,utils" convertir.R $1.utf8 $2 $3
echo "${1%.*}"
zip -j ${1%.*}.zip ${1%.*}.*
exit $?
