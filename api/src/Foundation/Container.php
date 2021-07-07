<?php

interface IComponentRegistration {
    public function asShared(): IComponentRegistration;
    public function withArguments(array $arguments): IComponentRegistration;
}

interface IContainer {
    public function get(string $name);
}

final class Component implements IComponentRegistration
{
    public string $type;
    public $target;
    public bool $isShared = false;
    public $instance = null;
    public $arguments = [];

    public function __construct(string $type, $target = null)
    {
        $this->type = $type;
        $this->target = $target ?? $type;
    }

    public function asShared(): IComponentRegistration
    {
        $this->isShared = true;
        return $this;
    }

    public function withArguments(array $arguments): IComponentRegistration
    {
        $this->arguments = $arguments;
        return $this;
    }
}

final class Container implements IContainer
{
    /** @var Component[] */
    private $components = [];

    public function add(string $type, $target = null)
    {
        return $this->components[$type] = new Component($type, $target);
    }

    public function get(string $name)
    {
        return $this->resolve($name, $this);
    }

    public function resolve(string $name, IContainer $container)
    {
        $component = $this->components[$name] ?? null;
        if ($component)
        {
            if ($component->isShared && $component->instance) {
                return $component->instance;
            }

            //Assume that target is class type name
            $reflection = new \ReflectionClass($component->target);
            $resolvedArguments = $this->resolveArguments($reflection, $container);
            $args = $component->arguments + $resolvedArguments;
            $instance = $reflection->newInstanceArgs($args);

            if ($component->isShared) {
                $component->instance = $instance;
            }

            return $instance;
        }
        else
        {
            $reflection = new \ReflectionClass($name);
            $args = $this->resolveArguments($reflection, $container);
            $instance = $reflection->newInstanceArgs($args);
            return $instance;
        }
    }

    private function resolveArguments(\ReflectionClass $reflection, IContainer $container)
    {
        $result = [];
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            foreach($constructor->getParameters() as $param)
            {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin())
                {
                    $result[$param->getName()] = $container->get($type->getName());
                }
            }
        }
        return $result;
    }
}

final class ResolutionScope implements IContainer
{
    private Container $container;
    private $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $name)
    {
        return $this->instances[$name] ??= $this->container->resolve($name, $this);
    }
}

// Example usage:

class Bar {

}

class Foo
{
    public function __construct(string $name, Bar $bar)
    {
        $this->name = $name;
        $this->bar = $bar;
    }
}

$container = new Container();
$container->add(Foo::class)->withArguments(["name" => "Cool"]);

$scope = new ResolutionScope($container);


$instance1 = $scope->get(Foo::class);
$instance2 = $scope->get(Foo::class);

// It's basic DI container implementation, but for now it's Okish

var_dump($instance1 === $instance2);