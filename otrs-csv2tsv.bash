#!/bin/bash
sed -e 's/","/	/g' -e 's/^"//' -e 's/"$//' -e 's/viikko//g' -e 's/#//g' \
    -e 's/	0	/	NA	/g' -e 's/	0$/	NA/'
