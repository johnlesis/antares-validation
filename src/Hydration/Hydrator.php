<?php

declare(strict_types=1);

namespace Antares\Hydration;

use Antares\Hydration\Exceptions\HydrationException;
use Antares\Validation\Validator;

final class Hydrator
{

    public function __construct(
        private readonly Validator $validator
    ) {}

    public function hydrate(string $className, array $data): object
    {
        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            throw new HydrationException("The class must have a constructor.");
        }

        $parameters = $constructor->getParameters();
        $args = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            if (!array_key_exists($name, $data)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                    continue;
                }
                if ($parameter->allowsNull()) {
                    $args[] = null;
                    continue;
                }
                throw new HydrationException("Missing required parameter: $name");
            }
            if ($type !== null && !$type->isBuiltin()) {
                $args[] = $this->hydrate($type->getName(), $data[$name]);
                continue;
            }
            if ($type->getName() === 'int') {
                if (filter_var($data[$name], FILTER_VALIDATE_INT) === false) {
                    throw new HydrationException("Parameter {$name} must be a valid integer.");
                }
                $args[] = (int) $data[$name];
                continue;
            }
            if ($type !== null && $type->getName() === 'float') {
                if (filter_var($data[$name], FILTER_VALIDATE_FLOAT) === false) {
                    throw new HydrationException("Parameter {$name} must be a valid float.");
                }
                $args[] = (float)$data[$name];
                continue;
            }
            if ($type !== null && $type->getName() === 'bool') {
                if (filter_var($data[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
                    throw new HydrationException("Parameter {$name} must be a valid boolean.");
                }
                $args[] = (bool)$data[$name];
                continue;
            }
            if ($type !== null && $type->getName() === 'string') {
                $args[] = (string)$data[$name];
                continue;
            }
            $args[] = $data[$name];
        }

        $instance =  $reflectionClass->newInstanceArgs($args);
        $this->validator->validate($instance);
        return $instance;
    }
}
