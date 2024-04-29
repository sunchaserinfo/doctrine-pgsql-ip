<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use PhpIP\IPv4;
use PhpIP\IPv4Block;
use PhpIP\IPv6;
use PhpIP\IPv6Block;
use SunChaser\Doctrine\PgSql\CidrType;
use SunChaser\Doctrine\PgSql\InetType;

require __DIR__ . '/../vendor/autoload.php';

Type::addType(InetType::NAME, InetType::class);
Type::addType(CidrType::NAME, CidrType::class);

$dsnParser = new \Doctrine\DBAL\Tools\DsnParser(['pgsql' => 'pdo_pgsql']);
$conn = DriverManager::getConnection(
    $dsnParser->parse('pgsql://postgres:T3cLgUr9KepiNmHXC9sBKs7b@localhost:2345/postgres')
);

$platform = $conn->getDatabasePlatform();
$platform->registerDoctrineTypeMapping(InetType::PG_TYPE, InetType::NAME);
$platform->registerDoctrineTypeMapping(CidrType::PG_TYPE, CidrType::NAME);

// drop tables
$conn->executeStatement('drop table if exists ip_demo');
$conn->executeStatement('drop table if exists ipblock_demo');
$conn->executeStatement('drop table if exists nullable_demo');

// create new
$conn->executeStatement('create table ip_demo(id serial constraint ip_demo_pk primary key, ip inet not null)');
$conn->executeStatement('create table ipblock_demo(id serial constraint ipblock_demo_pk primary key, ipblock cidr not null)');
$conn->executeStatement('create table nullable_demo(id serial constraint nullable_demo_pk primary key, ip inet, ipblock cidr)');

// check ip
$stmt = $conn->prepare('insert into ip_demo(ip) values (:ip)');

$stmt->bindValue('ip', IPv4::create('127.0.0.1'), InetType::NAME);
$stmt->executeStatement();

$stmt->bindValue('ip', IPv6::create('::1'), InetType::NAME);
$stmt->executeStatement();

$stmt->bindValue('ip', null, InetType::NAME);
try {
    $stmt->executeStatement();
} catch (Exception) {
    // skip
}

$stmt->bindValue('ip', IPv4Block::create('127.0.0.1/16'), InetType::NAME);
$stmt->executeStatement();

$stmt->bindValue('ip', IPv6Block::create('fc00::/64'), InetType::NAME);
$stmt->executeStatement();

$stmt = $conn->prepare('select * from ip_demo where id = :id');
$ids = [1,2,4,5];

foreach ($ids as $id) {
    $stmt->bindValue('id', $id);
    $result = $stmt->executeQuery();
    foreach ($result->iterateAssociative() as $row) {
        $value = Type::getType(InetType::NAME)->convertToPHPValue($row['ip'], $platform);
        var_dump($value::class);
        var_dump(strval($value));
    }
}

// check ipblock
$stmt = $conn->prepare('insert into ipblock_demo(ipblock) values (:ipblock)');

$stmt->bindValue('ipblock', null, CidrType::NAME);
try {
    $stmt->executeStatement();
} catch (Exception) {
    // skip
}

$stmt->bindValue('ipblock', IPv4Block::create('127.0.0.1/16'), CidrType::NAME);
$stmt->executeStatement();

$stmt->bindValue('ipblock', IPv6Block::create('fc00::/128'), CidrType::NAME);
$stmt->executeStatement();

$stmt = $conn->prepare('select * from ipblock_demo where id = :id');
$ids = [2,3];

foreach ($ids as $id) {
    $stmt->bindValue('id', $id);
    $result = $stmt->executeQuery();
    foreach ($result->iterateAssociative() as $row) {
        $value = Type::getType(CidrType::NAME)->convertToPHPValue($row['ipblock'], $platform);
        var_dump($value::class);
        var_dump(strval($value));
    }
}

// nullable
$stmt = $conn->prepare('insert into nullable_demo(ip, ipblock) values (:ip, :ipblock)');

$stmt->bindValue('ip', IPv4::create('127.0.0.1'), InetType::NAME);
$stmt->bindValue('ipblock', null, CidrType::NAME);
$stmt->executeStatement();

$stmt->bindValue('ip', null, InetType::NAME);
$stmt->bindValue('ipblock', IPv6Block::create('fc00::/128'), CidrType::NAME);
$stmt->executeStatement();

$stmt = $conn->prepare('select * from nullable_demo where id = :id');
$ids = [1,2];

foreach ($ids as $id) {
    $stmt->bindValue('id', $id);
    $result = $stmt->executeQuery();
    foreach ($result->iterateAssociative() as $row) {
        $value1 = Type::getType(InetType::NAME)->convertToPHPValue($row['ip'], $platform);
        $value2 = Type::getType(CidrType::NAME)->convertToPHPValue($row['ipblock'], $platform);
        var_dump(get_debug_type($value1), get_debug_type($value2));
        var_dump(strval($value1), strval($value2));
    }
}
