# Source from the Unidata LDM docker image, which runs CentOS 7
FROM unidata/ldm-docker:6.13.13

# Create the chswx user and group
RUN groupadd chswx -g 1001
RUN useradd chswx -u 1001 -g 1001
ENV CHSWX_HOME /home/chswx/
WORKDIR $CHSWX_HOME

# Install EPEL
RUN yum install -y epel-release

# Install remi repo for PHP 7.4
RUN wget http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
RUN rpm -Uvh remi-release-7.rpm

# Install PHP 7.4
RUN yum install -y php74-php-cli php-xml php-json

# Link PHP 7.4's CLI to /usr/bin/php
RUN ln /usr/bin/php74 /usr/bin/php

# Install the ingestor code
COPY src/ $CHSWX_HOME/services/ldm-ingest
