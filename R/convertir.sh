#!/bin/bash

iconv -f=Windows-1252 -t=UTF-8 $1 > $1.utf8
Rscript convertir.R $1.utf8 $2 $3
exit $?
