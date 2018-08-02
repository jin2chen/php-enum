<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

namespace jinchen\enum;

use BadMethodCallException;
use DomainException;
use ReflectionClass;
use InvalidArgumentException;
use LogicException;

/**
 * Class to implement enumerations for PHP.
 */
abstract class Enum
{
    /**
     * A map of enumerator names and values by enumeration class.
     *
     * @var array ["$class" => ["$name" => $value, ...], ...]
     */
    private static $constants = [];
    /**
     * A list of available enumerator names by enumeration class.
     *
     * @var array ["$class" => ["$name0", ...], ...]
     */
    private static $names = [];
    /**
     *  A list of available enumerator values by enumeration class.
     *
     * @var array ["$class" => [$value, ...], ...]
     */
    private static $values = [];
    /**
     * A map of enumerator names and values by enumeration class.
     *
     * @var array ["$class" => ["$name" => 'value', ...], ...]
     */
    private static $maps = [];
    /**
     * Already instantiated enumerators
     *
     * @var array ["$class" => ["$name" => $instance, ...], ...]
     */
    private static $instances = [];
    /**
     * The selected enumerator value
     *
     * @var mixed
     */
    private $value;
    /**
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name
     * @param mixed $value The value of the enumerator.
     */
    final private function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get an enumerator instance of the given enumerator value or instance.
     *
     * @param mixed $enumerator
     * @return static
     */
    final public static function get($enumerator)
    {
        if ($enumerator instanceof static && \get_class($enumerator) === static::class) {
            return $enumerator;
        }

        return static::byValue($enumerator);
    }

    /**
     * Get an enumerator instance by the given value.
     *
     * @param mixed $value The value of the enumerator.
     * @return static
     */
    final public static function byValue($value)
    {
        self::detectConstants(static::class);

        $name = \array_search($value, self::$maps[static::class], true);
        if ($name === false) {
            throw new InvalidArgumentException(sprintf(
                'Unknown value %s for enumeration %s',
                \is_scalar($value)
                    ? \var_export($value, true)
                    : 'of type ' . (\is_object($value) ? \get_class($value) : \gettype($value)),
                static::class
            ));
        }

        if (!isset(self::$instances[static::class][$name])) {
            self::$instances[static::class][$name] = new static($name, self::$constants[static::class][$name]);
        }

        return self::$instances[static::class][$name];
    }

    /**
     * Get an enumerator instance by the given name.
     *
     * @param string $name The name of the enumerator.
     * @return static
     */
    final public static function byName(string $name)
    {
        self::detectConstants(static::class);

        if (!isset(self::$constants[static::class][$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown name %s for enumeration %s',
                $name,
                static::class
            ));
        }

        if (!isset(self::$instances[static::class][$name])) {
            self::$instances[static::class][$name] = new static($name, self::$constants[static::class][$name]);
        }

        return self::$instances[static::class][$name];
    }

    /**
     * Get a list of enumerator instances ordered by ordinal number.
     *
     * @return static[]
     */
    final public static function enumerators()
    {
        self::detectConstants(static::class);

        return \array_map([static::class, 'byName'], self::$names[static::class]);
    }

    /**
     * Get a list of enumerator values.
     *
     * @return mixed[]
     */
    final public static function values()
    {
        self::detectConstants(static::class);

        return self::$values[static::class];
    }

    /**
     * Get a list of enumerator names.
     *
     * @return string[]
     */
    final public static function names()
    {
        self::detectConstants(static::class);

        return self::$names[static::class];
    }

    /**
     * Get all available constants of the called class.
     *
     * @return array
     */
    final public static function constants()
    {
        self::detectConstants(static::class);

        return self::$maps[static::class];
    }

    /**
     * Is the given enumerator part of this enumeration.
     *
     * @param mixed $value
     * @return bool
     */
    final public static function has($value)
    {
        if ($value instanceof static && \get_class($value) === static::class) {
            return true;
        }

        self::detectConstants(static::class);
        return \in_array($value, self::$maps[static::class], true);
    }

    /**
     * Get an enumerator instance by the given name.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined enumerator.
     *
     * @param string $method The name of the enumerator (called as method).
     * @param array $args
     * @return static
     */
    final public static function __callStatic($method, array $args)
    {
        $name = strtoupper(preg_replace('/(?|([a-z\d])([A-Z])|([^\^])([A-Z][a-z]))/', '$1_$2', $method));

        return self::byName($name);
    }

    /**
     * Detect all public available constants of given enumeration class.
     *
     * @param string $class
     * @return array
     */
    private static function detectConstants(string $class)
    {
        if (!isset(self::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            $publicConstants = [];

            do {
                $scopeConstants = [];
                foreach ($reflection->getReflectionConstants() as $reflConstant) {
                    if ($reflConstant->isPublic()) {
                        $scopeConstants[$reflConstant->getName()] = $reflConstant->getValue();
                    }
                }
                $publicConstants = $scopeConstants + $publicConstants;
            } while (($reflection = $reflection->getParentClass()) && $reflection->name !== __CLASS__);

            self::checkValues($publicConstants, $class);
            self::$constants[$class] = $publicConstants;
            self::$maps[$class] = \array_combine(\array_keys($publicConstants), \array_column($publicConstants, 'value'));
            self::$values[$class] = \array_values(self::$maps[$class]);
            self::$names[$class] = \array_keys(self::$maps[$class]);
        }

        return self::$constants[$class];
    }

    /**
     * Check the constant values is valid.
     *
     * @param array $constants
     * @param string $class
     * @throws DomainException
     */
    private static function checkValues(array $constants, string $class)
    {
        foreach ($constants as $name => $constant) {
            if (!is_array($constant)) {
                throw new DomainException(sprintf('Constant %s value is not an array.', $name));
            }

            if (!array_key_exists('value', $constant)) {
                throw new DomainException(sprintf('Constant %s value lost the \'value\' key.', $name));
            }
        }

        if (!self::noAmbiguousValues($constants)) {
            throw new DomainException(sprintf('The class %s contain ambiguous values.', $class));
        }
    }

    /**
     * Test that the given constants does not contain ambiguous values.
     *
     * @param array $constants
     * @return bool
     */
    private static function noAmbiguousValues(array $constants)
    {
        foreach ($constants as $value) {
            $names = \array_keys($constants, $value, true);
            if (\count($names) > 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the name of the enumerator.
     *
     * @return string
     */
    final public function name()
    {
        return $this->name;
    }

    /**
     * Get the value of the enumerator.
     *
     * @return mixed
     */
    final public function value()
    {
        return $this->value['value'];
    }

    /**
     * Compare this enumerator against another and check if it's the same.
     *
     * @param mixed $enumerator
     * @return bool
     */
    final public function is($enumerator)
    {
        return $this === $enumerator || $this->value['value'] === $enumerator

            // The following additional conditions are required only because of the issue of serializable singletons
            || ($enumerator instanceof static
                && \get_class($enumerator) === static::class
                && $enumerator->value['value'] === $this->value['value']
            );
    }

    /**
     * Get an special field value of the enumerator.
     *
     * e.g if you defined an enumerator value like ['value' => 1, 'label' => 'pending'],
     * then you can get value of label by $this->label().
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if (!isset($this->value[$name]) || !array_key_exists($name, $this->value)) {
            throw new BadMethodCallException();
        }

        return $this->value[$name];
    }

    /**
     * Get the name of the enumerator
     *
     * @return string
     * @see getName()
     */
    public function __toString()
    {
        return $this->name();
    }

    /**
     * @throws LogicException Enums are not serializable
     *         because instances are implemented as singletons.
     * @codeCoverageIgnore
     */
    final public function __sleep()
    {
        throw new LogicException('Enums are not serializable.');
    }

    /**
     * @throws LogicException Enums are not serializable
     *         because instances are implemented as singletons.
     * @codeCoverageIgnore
     */
    final public function __wakeup()
    {
        throw new LogicException('Enums are not serializable.');
    }

    /**
     * @throws LogicException Enums are not cloneable
     *         because instances are implemented as singletons.
     * @codeCoverageIgnore
     */
    final private function __clone()
    {
        throw new LogicException('Enums are not cloneable.');
    }
}
