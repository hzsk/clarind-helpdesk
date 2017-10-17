# clarind-helpdesk

Extra scripts, forms and stuff for [CLARIN-D
helpdesk](https://support.clarin-d.de/mail/index_test.php).

## Use of the support form

The official version is installed at [CLARIN-D
helpdesk web page](https://support.clarin-d.de/mail/index_test.php). It supports
following GET or POST parametres:

* `QueueID`, needs to be the *numeric* ID of the queue in ticketing system. If
  you have access to the system, you can find it from settings or the tickets per
  queue view, or by asking the administrators.
* `OwnerID`, needs to be the *numeric* ID of the owner of the ticket
* `ResponsibleID` needs to be the *numeric* ID of the responsible for the
  ticket.
* `lang` set language of the form: `de` for German or `en` for English.

Some queues have *string* aliases that can be addressed by `queue` parametre. If
you don't set the above parametres, the tickets will go to Helpdesk maintainers
for sorting.

## Installation of support form

This has not so far been installed outside the support.clarin-d.de, but it
should be possible to have your own version of the form. It's relatively simple
php script, just copy it over to a PHP supporting server, add PFBC, fill in your
passwords into /etc/clarin-helpdesk.conf and once you've tested it, change
$debugging = true to $debugging = false.


## R scripts

We have some R scripts that turn OTRS statistics into pretty figures. For
quarterly reporting and stuff.
