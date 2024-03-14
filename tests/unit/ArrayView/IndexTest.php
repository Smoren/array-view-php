<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Views\ArrayView;

class IndexTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForPositiveIndex
     * @param array $array
     * @param int $i
     * @param int $expected
     * @return void
     */
    public function testPositiveIndex(array $array, int $i, int $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $number = $arrayView[$i];

        // Then
        $this->assertSame($expected, $number);
    }

    public static function dataProviderForPositiveIndex(): array
    {
        return [
            [
                [10, 20, 30, 40, 50],
                0,
                10
            ],
            [
                [10, 20, 30, 40, 50],
                1,
                20
            ],
            [
                [10, 20, 30, 40, 50],
                2,
                30
            ],
            [
                [10, 20, 30, 40, 50],
                3,
                40
            ],
            [
                [10, 20, 30, 40, 50],
                4,
                50
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForPositiveStringIndex
     * @param array $array
     * @param string $i
     * @param int $expected
     * @return void
     */
    public function testPositiveStringIndex(array $array, string $i, int $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $number = $arrayView[$i];

        // Then
        $this->assertSame($expected, $number);
    }

    public static function dataProviderForPositiveStringIndex(): array
    {
        return [
            [
                [10, 20, 30, 40, 50],
                '0',
                10
            ],
            [
                [10, 20, 30, 40, 50],
                '1',
                20
            ],
            [
                [10, 20, 30, 40, 50],
                '2',
                30
            ],
            [
                [10, 20, 30, 40, 50],
                '3',
                40
            ],
            [
                [10, 20, 30, 40, 50],
                '4',
                50
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForNegativeIndex
     * @param array $array
     * @param int $i
     * @param int $expected
     * @return void
     */
    public function testNegativeIndex(array $array, int $i, int $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $number = $arrayView[$i];

        // Then
        $this->assertSame($expected, $number);
    }

    public static function dataProviderForNegativeIndex(): array
    {
        return [
            [
                [10, 20, 30, 40, 50],
                -0,
                10
            ],
            [
                [10, 20, 30, 40, 50],
                -1,
                50
            ],
            [
                [10, 20, 30, 40, 50],
                -2,
                40
            ],
            [
                [10, 20, 30, 40, 50],
                -3,
                30
            ],
            [
                [10, 20, 30, 40, 50],
                -4,
                20
            ],
            [
                [10, 20, 30, 40, 50],
                -5,
                10
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForNegativeStringIndex
     * @param array $array
     * @param string $i
     * @param int $expected
     * @return void
     */
    public function testNegativeStringIndex(array $array, string $i, int $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $number = $arrayView[$i];

        // Then
        $this->assertSame($expected, $number);
    }

    public static function dataProviderForNegativeStringIndex(): array
    {
        return [
            [
                [10, 20, 30, 40, 50],
                '-0',
                10
            ],
            [
                [10, 20, 30, 40, 50],
                '-1',
                50
            ],
            [
                [10, 20, 30, 40, 50],
                '-2',
                40
            ],
            [
                [10, 20, 30, 40, 50],
                '-3',
                30
            ],
            [
                [10, 20, 30, 40, 50],
                '-4',
                20
            ],
            [
                [10, 20, 30, 40, 50],
                '-5',
                10
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForIndexesLargerThanTwo
     * @param mixed $i
     * @return void
     */
    public function testPositiveIndexError($i): void
    {
        // Given
        $array = [10, 20, 30];
        $arrayView = ArrayView::toView($array);

        // Then
        $this->expectException(IndexError::class);
        $this->expectExceptionMessageMatches('/Index \d+ is out of range/');

        // When
        $number = $arrayView[$i];
    }

    public static function dataProviderForIndexesLargerThanTwo(): array
    {
        return [
            [3],
            ['3'],
            [4],
            ['4'],
            [100],
            ['100'],
        ];
    }

    /**
     * @dataProvider dataProviderForIndexesSmallerThanThanNegativeThree
     * @param mixed $i
     * @return void
     */
    public function testNegativeIndexError($i): void
    {
        // Given
        $array = [10, 20, 30];
        $arrayView = ArrayView::toView($array);

        // Then
        $this->expectException(IndexError::class);
        $this->expectExceptionMessageMatches('/Index -\d+ is out of range/');

        // When
        $number = $arrayView[$i];
    }

    public static function dataProviderForIndexesSmallerThanThanNegativeThree(): array
    {
        return [
            [-4],
            ['-4'],
            [-5],
            ['-5'],
            [-100],
            ['-100'],
        ];
    }

    /**
     * @dataProvider dataProviderForNonIntegerIndexes
     * @param mixed $i
     * @return void
     */
    public function testNonIntegerKeyError($i): void
    {
        // Given
        $array = [10, 20, 30];
        $arrayView = ArrayView::toView($array);

        // Then
        $this->expectException(KeyError::class);

        // When
        $number = $arrayView[$i];
    }

    public static function dataProviderForNonIntegerIndexes(): array
    {
        return [
            ['a'],
            ['１'],
            ['Ⅱ'],
            ['三'],
            ['④'],
            ['V'],
            ['six'],
        ];
    }

    /**
     * @dataProvider dataProviderForFloatIndexes
     * @param mixed $i
     * @return void
     */
    public function testNonIntegerIndexError($i): void
    {
        // Given
        $array = [10, 20, 30];
        $arrayView = ArrayView::toView($array);

        // Then
        $this->expectException(IndexError::class);

        // When
        $number = $arrayView[$i];
    }

    public static function dataProviderForFloatIndexes(): array
    {
        return [
            [0.1],
            ['0.5'],
            ['1.5'],
            [2.0],
            [3.1],
            ['45.66'],
            [\NAN],
            [\INF],
            [-\INF],
        ];
    }
}
