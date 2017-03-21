#!/bin/bash
sed -e 's/";"/	/g' -e 's/^"//' -e 's/"$//' -e 's/viikko//g'
