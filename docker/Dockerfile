# CLARIN-D helpdesk OTRS server dockerfile (beta)
# OTRS on ubuntu LTS with some customisations to configs and database from
# backup and some CLARIN brandings.

FROM ubuntu:16.04
MAINTAINER Tommi A Pirinen <tommi.antero.pirinen@uni-hamburg.de>

# <http://doc.otrs.com/doc/manual/admin/4.0/en/html/installation.html
# installation-on-debian>
# and results of : bin/otrs.CheckModules.pl on docker shell
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libapache2-mod-perl2 libdbd-mysql-perl libtimedate-perl \
    libnet-dns-perl libnet-ldap-perl libio-socket-ssl-perl libpdf-api2-perl \
    libdbd-mysql-perl libsoap-lite-perl libgd-text-perl libtext-csv-xs-perl \
    libjson-xs-perl libgd-graph-perl libapache-dbi-perl libarchive-zip-perl \
    libcrypt-eksblowfish-perl libcrypt-ssleay-perl libgd-gd2-perl \
    libgd-text-perl libgd-graph-perl libjson-xs-perl libmail-imapclient-perl \
    libauthen-ntlm-perl libapache2-mod-perl2 libnet-dns-perl libpdf-api2-perl\
    libtemplate-perl libtext-csv-xs-perl libxml-parser-perl \
    libyaml-libyaml-perl \
    language-pack-de-base \
    wget \
    apache2 \
    mysql-server \
    cron
RUN update-locale LANG=de_DE.UTF-8 LANGUAGE=de:en
RUN wget http://ftp.otrs.org/pub/otrs/otrs-4.0.28.tar.gz && \
    tar zxvf otrs-4.0.28.tar.gz && \
    mv otrs-4.0.28 /opt/otrs
RUN useradd -r otrs -d /opt/otrs
RUN cp /opt/otrs/Kernel/Config.pm.dist /opt/otrs/Kernel/Config.pm && \
    cp /opt/otrs/Kernel/Config/GenericAgent.pm.dist \
    /opt/otrs/Kernel/Config/GenericAgent.pm && \
    cp /opt/otrs/var/cron/scheduler_watchdog.dist \
    /opt/otrs/var/cron/scheduler_watchdog && \
    cp /opt/otrs/scripts/apache2-httpd.include.conf \
    /etc/apache2/conf-enabled/zzz_otrs.conf
RUN perl -cw /opt/otrs/bin/cgi-bin/index.pl && \
    perl -cw /opt/otrs/bin/cgi-bin/customer.pl && \
    perl -cw /opt/otrs/bin/otrs.PostMaster.pl
RUN /opt/otrs/bin/otrs.SetPermissions.pl --web-group=www-data
RUN a2enmod rewrite && \
    /etc/init.d/apache2 restart
RUN sed -i -e 's/max_allowed_packet.*/max_allowed_packet = 32M/' \
    -e  '/[[]mysqld[]]/a innodb_log_file_size = 512M' /etc/mysql/my.cnf &&\
    /etc/init.d/mysql restart
USER otrs
RUN whoami
RUN /opt/otrs/bin/Cron.sh start
EXPOSE 80 8080


