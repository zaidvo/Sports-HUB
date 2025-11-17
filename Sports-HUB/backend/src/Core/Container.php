<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

final class Container
{
    /**
     * @var array<string, Closure(self): mixed>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @template T
     *
     * @param class-string<T> $id
     * @param Closure(self):T $factory
     */
    public function set(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->instances)) {
            /** @var T */
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->bindings)) {
            throw new RuntimeException("No binding registered for {$id}");
        }

        $this->instances[$id] = $this->bindings[$id]($this);

        /** @var T */
        return $this->instances[$id];
    }
}

