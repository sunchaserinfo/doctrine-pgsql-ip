<?php

declare(strict_types=1);

namespace SunChaser\Doctrine\PgSql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use PhpIP\IP;
use PhpIP\IPBlock;

final class InetType extends Type
{
    public const PG_TYPE = 'inet';
    public const NAME = self::PG_TYPE;

    /**
     * @inheritDoc
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): IP|IPBlock|null
    {
        if ($value === null || $value instanceof IP || $value instanceof IPBlock) {
            return $value;
        }

        try {
            return str_contains($value, '/') ? IPBlock::create($value) : IP::create($value);
        } catch (InvalidArgumentException $e) {
            throw ValueNotConvertible::new($value, self::NAME, null, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return match (true) {
            $value === null => null,
            $value instanceof IP => $value->humanReadable(),
            $value instanceof IPBlock => $value->withPrefixLength(),
            default => throw InvalidType::new($value, self::NAME, ['null', IP::class, IPBlock::class])
        };
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDoctrineTypeMapping(self::PG_TYPE);
    }
}
