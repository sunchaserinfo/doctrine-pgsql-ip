# IP Address types for Doctrine

This library adds support for `cidr` and `inet` types of PostgreSQL in Doctrine using the [leth/ip-address] library.

[leth/ip-address]: https://packagist.org/packages/leth/ip-address

## Installation

    composer require sunchaser/doctrine-pgsql-ip

## Usage

1. Register types in Doctrine

   ```php
   <?php

   \Doctrine\DBAL\Types\Type::addType('inet', \SunChaser\Doctrine\PgSql\InetType::class);
   \Doctrine\DBAL\Types\Type::addType('cidr', \SunChaser\Doctrine\PgSql\CidrType::class);
   ```

2. Add type handling for schema operations

   ```php
   <?php

   $conn = $em->getConnection();
   $conn->getDatabasePlatform()->registerDoctrineTypeMapping('inet', 'inet');
   $conn->getDatabasePlatform()->registerDoctrineTypeMapping('cidr', 'cidr');
   ```

`inet` accepts and retrieves both `\Leth\IPAddress\IP\Address` for individual addresses
and `\Leth\IPAddress\IP\NetworkAddress` for network masks.
Please check the type when retrieving the data.

`cidr` accepts and retrieves only `\Leth\IPAddress\IP\NetworkAddress`.
All nonzero bits to the right of the netmask will be discarded on save.
