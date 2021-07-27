<?php

namespace SunChaser\Doctrine\PgSql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Leth\IPAddress\IP\NetworkAddress;

class CidrType extends Type
{
    const CIDR = 'cidr';

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof NetworkAddress) {
            return $value;
        }

        try {
            return NetworkAddress::factory($value);
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof NetworkAddress) {
            return strval($value->get_network_start()) . '/' . strval($value->get_cidr());
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', NetworkAddress::class]
        );
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping(static::CIDR);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return static::CIDR;
    }
}
