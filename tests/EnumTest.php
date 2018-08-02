<?php

namespace jinchen\test;

use BadMethodCallException;
use DomainException;
use InvalidArgumentException;
use jinchen\enum\Enum;
use jinchen\test\assets\AmbiguousEnum;
use jinchen\test\assets\InvalidValueEnum;
use jinchen\test\assets\LostValueEnum;
use jinchen\test\assets\RequestEnum;
use jinchen\test\assets\StatusEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

class EnumTest extends TestCase
{
    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function setUp()
    {
        $this->resetStaticEnumProps();
    }

    /**
     * Un-initialize all known enumerations.
     *
     * @throws \ReflectionException
     */
    private function resetStaticEnumProps()
    {
        $enumRefl = new ReflectionClass(Enum::class);
        $enumPropsRefl = $enumRefl->getProperties(ReflectionProperty::IS_STATIC);
        foreach ($enumPropsRefl as $enumPropRefl) {
            $enumPropRefl->setAccessible(true);
            $enumPropRefl->setValue([]);
        }
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     */
    public function valid()
    {
        $activeValue = 1;
        $inactiveValue = 0;
        $activeLabel = 'active';
        $inactiveLabel = 'inactive';
        $activeName = 'ACTIVE';
        $inactiveName = 'INACTIVE';

        $this->assertEquals([$activeValue, $inactiveValue], StatusEnum::values());
        $this->assertEquals([$activeName, $inactiveName], StatusEnum::names());
        $this->assertEquals([$activeName => $activeValue, $inactiveName => $inactiveValue], StatusEnum::constants());
        $this->assertInstanceOf(StatusEnum::class, StatusEnum::active());
        $this->assertEquals(StatusEnum::active(), StatusEnum::get($activeValue));
        $this->assertEquals(StatusEnum::active(), StatusEnum::byValue($activeValue));
        $this->assertEquals(StatusEnum::active(), StatusEnum::byName($activeName));
        $this->assertEquals([StatusEnum::active(), StatusEnum::inactive()], StatusEnum::enumerators());
        $this->assertTrue(StatusEnum::has($activeValue));

        $this->assertEquals($activeValue, StatusEnum::active()->value());
        $this->assertEquals($activeLabel, StatusEnum::active()->label());
        $this->assertEquals($inactiveLabel, StatusEnum::inactive()->label());
        $this->assertEquals($activeName, StatusEnum::active()->name());
        $this->assertEquals($activeName, StatusEnum::active() . '');
        $this->assertTrue(StatusEnum::get($activeValue)->is($activeValue));
        $this->assertTrue(StatusEnum::get($activeValue)->is(StatusEnum::active()));

        $pendingValue = 'pending';
        $this->assertEquals($pendingValue, RequestEnum::fisPending()->value());
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException DomainException
     */
    public function lostValueField()
    {
        LostValueEnum::values();
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException DomainException
     */
    public function invalidDefinedValue()
    {
        InvalidValueEnum::values();
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException InvalidArgumentException
     */
    public function invalidName()
    {
        StatusEnum::byName('AAAA');
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException InvalidArgumentException
     */
    public function invalidValue()
    {
        StatusEnum::byValue(100);
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException BadMethodCallException
     */
    public function badMethodCall()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        StatusEnum::active()->upperLabel();
    }

    /**
     * @test
     * @covers \jinchen\enum\Enum
     * @expectedException DomainException
     */
    public function ambiguous()
    {
        AmbiguousEnum::active();
    }
}