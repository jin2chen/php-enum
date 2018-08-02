# php-enum
This is a native PHP implementation to add enumeration support to PHP >= 7.1.
It's an abstract class that needs to be extended to use it.

# Usage
## Basics
```php
/**
 * Class StatusEnum
 *
 * @method static $this active()
 * @method static $this inactive()
 * @method string label()
 */
class StatusEnum extends Enum
{
    public const ACTIVE = ['value' => 1, 'label' => 'active'];
    public const INACTIVE = ['value' => 0, 'label' => 'inactive'];
}

// ways to instantiate an enumerator
$status = StatusEnum::get(StatusEnum::ACTIVE['value']); // by value or instance
$status = StatusEnum::active();                         // by name as callable
$status = StatusEnum::byValue('1');                      // by value
$status = StatusEnum::byName('ACTIVE');                 // by name

// basic methods of an instantiated enumerator
$status->getValue();   // 1
$status->getName();    // ACTIVE

// basic methods to list defined enumerators
StatusEnum::enumerators();  // returns a list of enumerator instances
StatusEnum::values();       // returns a list of enumerator values['value']
StatusEnum::names();        // returns a list of enumerator names
StatusEnum::constants();    // returns an associative array of enumerator names to enumerator values

// same enumerators (of the same enumeration class) holds the same instance
StatusEnum::get(StatusEnum::ACTIVE['value']) === StatusEnum::ACTIVE()
StatusEnum::get(StatusEnum::ACTIVE['value']) != StatusEnum::INACTIVE()

// simplified way to compare two enumerators
$status = StatusEnum::active();
$status->is(StatusEnum::ACTIVE['value']);      // true
$status->is(StatusEnum::active());             // true
$status->is(StatusEnum::INACTIVE['value']);    // false
$status->is(StatusEnum::inactive());           // false
```
