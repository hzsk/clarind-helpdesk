# clarind-helpdesk

Extra scripts, forms and stuff for [CLARIN-D
helpdesk](https://support.clarin-d.de/mail/index_test.php).

## Installation of support form

This has not so far been installed outside the support.clarin-d.de, but it
should be possible to have your own version of the form. It's relatively simple
php script, just copy it over to a PHP supporting server, add PFBC, fill in your
passwords into /etc/clarin-helpdesk.conf and once you've tested it, change
$debugging = true to $debugging = false.

## R scripts

We have some R scripts that turn OTRS statistics into pretty figures. For
quarterly reporting and stuff.
