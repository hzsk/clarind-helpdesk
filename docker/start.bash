#!/bin/bash

# start OTRS on docker
echo starting OTRS:
echo mysqld_safe
mysqld_safe &
echo apache2
apache2ctl start &
echo done.
