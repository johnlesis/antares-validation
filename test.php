<?php

use Antares\Hydration\Hydrator;
use Antares\Validation\Validator;
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\Min;
use Antares\Validation\Exceptions\ValidationException;
use Antares\Hydration\Exceptions\HydrationException;

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

