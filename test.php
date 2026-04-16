<?php

use Antares\Hydration\Hydrator;
use Antares\Validation\Validator;
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\Min;
use Antares\Validation\Exceptions\ValidationException;
use Antares\Hydration\Exceptions\HydrationException;
use Antares\Validation\Attributes\Strict;

require 'vendor/autoload.php';

final readonly class CreatePatientRequest
{
    public function __construct(
        #[MinLength(5)]
        public string $name,

        #[Min(0)]
        public int $age,
    ) {}
}

$hydrator = new Hydrator(new Validator());

try {
    $dto = $hydrator->hydrate(CreatePatientRequest::class, [
        'name' => 'Jo',
        'age'  => -1,
    ]);
} catch (ValidationException $e) {
    print_r($e->getErrors());
} catch (HydrationException $e) {
    echo $e->getMessage();
}

try {
    $dto = $hydrator->hydrate(CreatePatientRequest::class, [
        'name' => 'J',
        'age'  => -5,
    ]);
    var_dump($dto);
} catch (ValidationException $e) {
    print_r($e->getErrors());
} catch (HydrationException $e) {
    echo $e->getMessage();
}

// test strict mode
#[Strict]
final readonly class StrictDTO
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

// should throw - extra field
try {
    $dto = $hydrator->hydrate(StrictDTO::class, [
        'name' => 'John',
        'age'  => 25,
        'extra' => 'not allowed',
    ]);
} catch (HydrationException $e) {
    echo $e->getMessage() . "\n";
}

// should work - no extra fields
try {
    $dto = $hydrator->hydrate(StrictDTO::class, [
        'name' => 'John',
        'age'  => 25,
    ]);
    echo "OK: " . $dto->name . "\n";
} catch (HydrationException $e) {
    echo $e->getMessage() . "\n";
}