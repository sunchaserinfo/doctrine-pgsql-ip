<?php

namespace SunChaser\Doctrine\PgSql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Leth\IPAddress\IP\Address;
use Leth\IPAddress\IP\NetworkAddress;

class InetType extends Type
{
    const INET = 'inet';

    /**
     * @inheritDoc
     * @return Address|NetworkAddress|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof Address || $value instanceof NetworkAddress) {
            return $value;
        }

        try {
            if (strpos($value, '/') !== false) {
                return NetworkAddress::factory($value);
            } else {
                return Address::factory($value);
            }
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }

    /**
     * @inheritDoc
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof Address || $value instanceof NetworkAddress) {
            return strval($value);
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', Address::class, NetworkAddress::class]
        );
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getDoctrineTypeMapping(static::INET);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName()
    {
        return static::INET;
    }
}
