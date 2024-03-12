<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Structs;

use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Structs\NormalizedSlice;
use Smoren\ArrayView\Structs\Slice;

class SliceTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForTrue
     */
    public function testIsSliceTrue(string $input)
    {
        $this->assertTrue(Slice::isSlice($input));
        $this->assertTrue(Slice::isSliceString($input));
    }

    /**
     * @dataProvider dataProviderForFalse
     */
    public function testIsSliceFalse($input)
    {
        $this->assertFalse(Slice::isSlice($input));
        $this->assertFalse(Slice::isSliceString($input));
    }

    /**
     * @dataProvider dataProviderForFalse
     */
    public function testSliceError($input)
    {
        $this->expectException(ValueError::class);
        $strInput = \is_scalar($input) ? "{$input}" : \gettype($input);
        $this->expectExceptionMessage("Invalid slice: \"{$strInput}\"");

        Slice::toSlice($input);
    }

    /**
     * @dataProvider dataProviderForToSlice
     */
    public function testToSlice($input, array $expected)
    {
        $actual = Slice::toSlice($input);
        $expectedSlice = new Slice(...$expected);

        $this->assertSame($expectedSlice->start, $actual->start);
        $this->assertSame($expectedSlice->end, $actual->end);
        $this->assertSame($expectedSlice->step, $actual->step);
    }

    /**
     * @dataProvider dataProviderForSliceToString
     */
    public function testSliceToString(string $input, string $expected)
    {
        $slice = Slice::toSlice($input);
        $this->assertSame($expected, $slice->toString());
    }

    /**
     * @dataProvider dataProviderForSliceNormalize
     */
    public function testSliceNormalize(string $input, int $size, string $expected, array $expectedIndexes)
    {
        $slice = Slice::toSlice($input);
        $normalizedSlice = $slice->normalize($size);

        $this->assertInstanceOf(NormalizedSlice::class, $normalizedSlice);
        $this->assertSame($expected, $normalizedSlice->toString());
        $this->assertSame($expectedIndexes, [...$normalizedSlice]);
    }

    /**
     * @dataProvider dataProviderForIsSliceArrayTrue
     */
    public function testIsSliceArrayTrue(array $input)
    {
        $this->assertTrue(Slice::isSliceArray($input));
    }

    /**
     * @dataProvider dataProviderForIsSliceArrayFalse
     */
    public function testIsSliceArrayFalse($input)
    {
        $this->assertFalse(Slice::isSliceArray($input));
    }

    public function dataProviderForTrue(): array
    {
        return [
            [':'],
            ['::'],
            ['0:'],
            ['1:'],
            ['-1:'],
            ['0::'],
            ['1::'],
            ['-1::'],
            [':0'],
            [':1'],
            [':-1'],
            [':0:'],
            [':1:'],
            [':-1:'],
            ['0:0:'],
            ['1:1:'],
            ['-1:-1:'],
            ['1:1:-1'],
            ['-1:-1:-1'],
            ['1:2:3'],
        ];
    }

    public function dataProviderForFalse(): array
    {
        return [
            [''],
            ['0'],
            ['1'],
            ['1:::'],
            [':1::'],
            ['::1:'],
            [':::1'],
            ['test'],
            ['[::]'],
            ['a:b:c'],
            [0],
            [1],
            [1.1],
            [true],
            [false],
            [null],
            [new \ArrayObject([])],
            [['a' => 1]],
        ];
    }

    public function dataProviderForToSlice(): array
    {
        return [
            [':', [null, null, null]],
            ['::', [null, null, null]],
            ['0:', [0, null, null]],
            ['1:', [1, null, null]],
            ['-1:', [-1, null, null]],
            ['0::', [0, null, null]],
            ['1::', [1, null, null]],
            ['-1::', [-1, null, null]],
            [':0', [null, 0, null]],
            [':1', [null, 1, null]],
            [':-1', [null, -1, null]],
            [':0:', [null, 0, null]],
            [':1:', [null, 1, null]],
            [':-1:', [null, -1, null]],
            ['0:0:', [0, 0, null]],
            ['1:1:', [1, 1, null]],
            ['-1:-1:', [-1, -1, null]],
            ['1:1:-1', [1, 1, -1]],
            ['-1:-1:-1', [-1, -1, -1]],
            ['1:2:3', [1, 2, 3]],
        ];
    }

    public function dataProviderForSliceToString(): array
    {
        return [
            [':', '::'],
            ['::', '::'],
            ['0:', '0::'],
            ['1:', '1::'],
            ['-1:', '-1::'],
            ['0::', '0::'],
            ['1::', '1::'],
            ['-1::', '-1::'],
            [':0', ':0:'],
            [':1', ':1:'],
            [':-1', ':-1:'],
            [':0:', ':0:'],
            [':1:', ':1:'],
            [':-1:', ':-1:'],
            ['0:0:', '0:0:'],
            ['1:1:', '1:1:'],
            ['-1:-1:', '-1:-1:'],
            ['1:1:-1', '1:1:-1'],
            ['-1:-1:-1', '-1:-1:-1'],
            ['1:2:3', '1:2:3'],
        ];
    }

    public function dataProviderForSliceNormalize(): array
    {
        return [
            [':', 0, '0:0:1', []],
            ['::', 1, '0:1:1', [0]],
            ['0:', 2, '0:2:1', [0, 1]],
            ['1:', 5, '1:5:1', [1, 2, 3, 4]],
            ['-1:', 3, '2:3:1', [2]],
            ['0::', 10, '0:10:1', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]],
            ['1::', 0, '0:0:1', []],
            ['-1::', 0, '0:0:1', []],
            [':0', 1, '0:0:1', []],
            [':1', 2, '0:1:1', [0]],
            [':-1', 5, '0:4:1', [0, 1, 2, 3]],
            [':0:', 3, '0:0:1', []],
            [':1:', 1, '0:1:1', [0]],
            [':-1:', 3, '0:2:1', [0, 1]],
            ['0:0:', 3, '0:0:1', []],
            ['1:1:', 3, '1:1:1', []],
            ['-1:-1:', 10, '9:9:1', []],
            ['1:1:-1', 10, '1:1:-1', []],
            ['-1:-1:-1', 10, '9:9:-1', []],
            ['1:2:3', 10, '1:2:3', [1]],
            ['1:2:3', 1, '0:0:3', []],
            ['::-1', 1, '0:-1:-1', [0]],
            ['1::-1', 1, '0:-1:-1', [0]],
            ['2::-1', 1, '0:-1:-1', [0]],
            ['2:-3:-1', 1, '0:-1:-1', [0]],
            ['2::-1', 10, '2:-1:-1', [2, 1, 0]],
            [':3:-1', 10, '9:3:-1', [9, 8, 7, 6, 5, 4]],
        ];
    }

    public function dataProviderForIsSliceArrayTrue(): array
    {
        return [
            [[]],
            [[null, null]],
            [[null, null, null]],
            [[0]],
            [[0, null]],
            [[0, null, null]],
            [[1, null, null]],
            [[1, 0, null]],
            [[1, 1, null]],
            [[-1, 1, null]],
            [[1, null, 1]],
            [[1, null, 2]],
            [[null, null, 1]],
            [[null, null, -1]],
            [[1, 10, -1]],
        ];
    }

    public function dataProviderForIsSliceArrayFalse(): array
    {
        return [
            [['']],
            [['a']],
            [[0, 1, 'a']],
            [[0, 1, 2, 3]],
            [[1.1, 1, 2]],
            [[1, 1, 2.2]],
            [null],
            [0],
            [1],
            [0.0],
            [1.0],
            [true],
            [false],
            [new \ArrayObject([])],
            [['a' => 1]],
            [[[]]],
            [[['a' => 1]]],
        ];
    }
}
