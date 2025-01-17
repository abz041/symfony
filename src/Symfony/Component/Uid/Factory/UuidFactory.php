<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uid\Factory;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV5;
use Symfony\Component\Uid\UuidV6;

class UuidFactory
{
    private string $defaultClass;
    private string $timeBasedClass;
    private string $nameBasedClass;
    private string $randomBasedClass;
    private ?Uuid $timeBasedNode;
    private ?Uuid $nameBasedNamespace;

    public function __construct(string|int $defaultClass = UuidV6::class, string|int $timeBasedClass = UuidV6::class, string|int $nameBasedClass = UuidV5::class, string|int $randomBasedClass = UuidV4::class, Uuid|string $timeBasedNode = null, Uuid|string $nameBasedNamespace = null)
    {
        if (null !== $timeBasedNode && !$timeBasedNode instanceof Uuid) {
            $timeBasedNode = Uuid::fromString($timeBasedNode);
        }

        if (null !== $nameBasedNamespace) {
            $nameBasedNamespace = $this->getNamespace($nameBasedNamespace);
        }

        $this->defaultClass = is_numeric($defaultClass) ? Uuid::class.'V'.$defaultClass : $defaultClass;
        $this->timeBasedClass = is_numeric($timeBasedClass) ? Uuid::class.'V'.$timeBasedClass : $timeBasedClass;
        $this->nameBasedClass = is_numeric($nameBasedClass) ? Uuid::class.'V'.$nameBasedClass : $nameBasedClass;
        $this->randomBasedClass = is_numeric($randomBasedClass) ? Uuid::class.'V'.$randomBasedClass : $randomBasedClass;
        $this->timeBasedNode = $timeBasedNode;
        $this->nameBasedNamespace = $nameBasedNamespace;
    }

    public function create(): UuidV6|UuidV4|UuidV1
    {
        $class = $this->defaultClass;

        return new $class();
    }

    public function randomBased(): RandomBasedUuidFactory
    {
        return new RandomBasedUuidFactory($this->randomBasedClass);
    }

    public function timeBased(Uuid|string $node = null): TimeBasedUuidFactory
    {
        $node ??= $this->timeBasedNode;

        if (null !== $node && !$node instanceof Uuid) {
            $node = Uuid::fromString($node);
        }

        return new TimeBasedUuidFactory($this->timeBasedClass, $node);
    }

    public function nameBased(Uuid|string $namespace = null): NameBasedUuidFactory
    {
        $namespace ??= $this->nameBasedNamespace;

        if (null === $namespace) {
            throw new \LogicException(sprintf('A namespace should be defined when using "%s()".', __METHOD__));
        }

        return new NameBasedUuidFactory($this->nameBasedClass, $this->getNamespace($namespace));
    }

    private function getNamespace(Uuid|string $namespace): Uuid
    {
        if ($namespace instanceof Uuid) {
            return $namespace;
        }

        switch ($namespace) {
            case 'dns': return new UuidV1(Uuid::NAMESPACE_DNS);
            case 'url': return new UuidV1(Uuid::NAMESPACE_URL);
            case 'oid': return new UuidV1(Uuid::NAMESPACE_OID);
            case 'x500': return new UuidV1(Uuid::NAMESPACE_X500);
            default: return Uuid::fromString($namespace);
        }
    }
}
