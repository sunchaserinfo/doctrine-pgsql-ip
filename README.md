# IP Address types for Doctrine

This library adds support for `cidr` and `inet` types of PostgreSQL in Doctrine using the [rlanvin/php-ip] library.

[rlanvin/php-ip]: https://github.com/rlanvin/php-ip

## Installation

    composer require sunchaser/doctrine-pgsql-ip

## Usage

1. Register types in Doctrine

   ```php
   <?php
   
   use Doctrine\DBAL\Types\Type;
   use SunChaser\Doctrine\PgSql\InetType;
   use SunChaser\Doctrine\PgSql\CidrType;

   Type::addType(InetType::NAME, InetType::class);
   Type::addType(CidrType::NAME, CidrType::class);
   ```

2. Add type handling for schema operations

   ```php
   <?php

   use Doctrine\DBAL\Connection;
   use SunChaser\Doctrine\PgSql\InetType;
   use SunChaser\Doctrine\PgSql\CidrType;
   
   /** @var Connection $conn */
   $conn->getDatabasePlatform()->registerDoctrineTypeMapping(InetType::PG_TYPE, InetType::NAME);
   $conn->getDatabasePlatform()->registerDoctrineTypeMapping(CidrType::PG_TYPE, CidrType::NAME);
   ```

`inet` accepts and retrieves both `\PhpIP\IP` for individual addresses
and `\PhpIP\IPBlock` for network masks.
Please check the type when retrieving the data.

`cidr` accepts and retrieves only `\PhpIP\IPBlock`.

## Upgrade

Changes in 2.0:

* `leth/ip-address` was replaced with `rlanvin/php-ip`
* Requirements were bumped to PHP 8.0 and Doctrine DBAL 3.0
