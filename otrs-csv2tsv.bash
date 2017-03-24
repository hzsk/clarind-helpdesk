#!/bin/bash
E=0
if test $# -ge 1 ; then
    E=$1
fi
sed -e 's/","/	/g' -e 's/^"//' -e 's/"$//' -e 's/viikko//g' -e 's/#//g' \
    -e "s/	0	/	$E	/g" -e "s/	0	/	$E	/g" -e "s/	0$/	$E/" \
    -e 's/	$//'
