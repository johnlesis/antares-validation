# Antares Validation

Lightweight attribute-based DTO hydration and validation for PHP 8.2+

Define your request shape once and get hydration and validation automatically.

---

# Installation

```bash
composer require fatjon-lleshi/antares-validation
```

---

# Quick Start

```php
use Antares\Hydration\Hydrator;
use Antares\Validation\Validator;
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\Min;
use Antares\Validation\Exceptions\ValidationException;
use Antares\Hydration\Exceptions\HydrationException;

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

$data = json_decode(file_get_contents('php://input'), true);

try {
    $dto = $hydrator->hydrate(CreatePatientRequest::class, $data);

    // DTO is ready to use

} catch (ValidationException $e) {

    // Validation errors
    $errors = $e->getErrors();

} catch (HydrationException $e) {

    // Missing or invalid fields
}
```

---

# Validation Errors

When validation fails, a structured array is returned keyed by field name.

```php
try {
    $dto = $hydrator->hydrate(CreatePatientRequest::class, [
        'name' => 'Jo',
        'age'  => -1,
    ]);
} catch (ValidationException $e) {
    print_r($e->getErrors());
}
```

Output

```php
Array
(
    [name] => Array
        (
            [0] => The value must be at least 5 characters long.
        )

    [age] => Array
        (
            [0] => The value must be at least 0.
        )
)
```

---

# Examples

## Nullable and Optional Fields

```php
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\MaxLength;
use Antares\Validation\Attributes\Email;

final readonly class UpdatePatientRequest
{
    public function __construct(
        #[MinLength(2)]
        #[MaxLength(100)]
        public string $name,

        #[Email]
        public string $email,

        public ?string $notes = null,
        public string $ward = 'general',
    ) {}
}
```

---

## Nested DTOs

```php
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\Pattern;

final readonly class AddressDTO
{
    public function __construct(
        public string $street,
        public string $city,

        #[Pattern('/^\d{5}$/')]
        public string $postalCode,
    ) {}
}

final readonly class CreatePatientRequest
{
    public function __construct(
        #[MinLength(2)]
        public string $name,

        public AddressDTO $address,
    ) {}
}
```

Nested DTOs are hydrated and validated automatically.

---

## Enum Validation

```php
use Antares\Validation\Attributes\MinLength;
use Antares\Validation\Attributes\InEnum;

enum WardType: string
{
    case ICU = 'icu';
    case General = 'general';
    case Pediatric = 'pediatric';
}

final readonly class CreatePatientRequest
{
    public function __construct(
        #[MinLength(2)]
        public string $name,

        #[InEnum(WardType::class)]
        public string $ward,
    ) {}
}
```

---

# Using with PSR-7

```php
$data = json_decode((string) $request->getBody(), true);

$dto = $hydrator->hydrate(CreatePatientRequest::class, $data);
```

---

# Using with Symfony

```php
$data = json_decode($request->getContent(), true);

$dto = $hydrator->hydrate(CreatePatientRequest::class, $data);
```

---

# Antares Framework

When used with the Antares framework (work in progress), hydration and validation happen automatically.
Just type-hint your DTO in the controller.

```php
public function create(CreatePatientRequest $request)
{
    // Already hydrated and validated
}
```

---

# Available Attributes

`#[Email]` Validate email format  
`#[InEnum(MyEnum::class)]` Must match enum value  
`#[Max(100)]` Maximum number value  
`#[Min(0)]` Minimum number value  
`#[MaxLength(100)]` Maximum string length  
`#[MinLength(2)]` Minimum string length  
`#[NotBlank]` Must not be empty  
`#[Pattern('/regex/')]` Match regex pattern

---

# Exceptions

## ValidationException

Thrown when validation fails.

```php
$errors = $e->getErrors();
```

Returns

```php
[
    'field' => ['Error message']
]
```

---

## HydrationException

Thrown when:

- Missing fields
- Invalid types
- Malformed input

---

# Requirements

- PHP 8.2+
- Composer
