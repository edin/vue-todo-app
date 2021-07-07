<?php

namespace App\Foundation\Container;

final class Container implements IContainer
{
    /** @var Component[] */
    private $components = [];

    public function add(string $type, $target = null, bool $shared = false, array $arguments = [])
    {
        $component = new Component($type, $target);
        return $this->components[$type] = $component->arguments($arguments);
    }

    public function get(string $name)
    {
        return $this->resolve($name, $this);
    }

    public function resolve(string $name, IContainer $container)
    {
        $instance = null;
        $component = $this->components[$name] ?? null;
        if ($component) {
            if ($component->isShared && $component->instance) {
                return $component->instance;
            }

            if (is_string($component->target)) {
                $reflection = new \ReflectionClass($component->target);
                $resolvedArguments = $this->resolveArguments($reflection, $container);
                $args = $component->arguments + $resolvedArguments;
                $instance = $reflection->newInstanceArgs($args);
            } elseif ($component->target instanceof \Closure) {
                $instance = ($component->target)();
            } else {
                $instance = $component->target;
            }

            if ($component->isShared) {
                $component->instance = $instance;
            }
        } else {
            $reflection = new \ReflectionClass($name);
            $args = $this->resolveArguments($reflection, $container);
            $instance = $reflection->newInstanceArgs($args);
        }
        return $instance;
    }

    private function resolveArguments(\ReflectionClass $reflection, IContainer $container)
    {
        $result = [];
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $result[$param->getName()] = $container->get($type->getName());
                }
            }
        }
        return $result;
    }
}
