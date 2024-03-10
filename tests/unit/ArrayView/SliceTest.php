<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Views\ArrayView;

class SliceTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForSliceWholeArray
     * @param array $array
     * @return void
     */
    public function testSliceWholeArraySingleColon(array $array): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView[':'];

        // Then
        $this->assertSame($array, $slice);
    }

    /**
     * @dataProvider dataProviderForSliceWholeArray
     * @param array $array
     * @return void
     */
    public function testSliceWholeArrayDoubleColon(array $array): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView['::'];

        // Then
        $this->assertSame($array, $slice);
    }

    public static function dataProviderForSliceWholeArray(): array
    {
        return [
            [
                []
            ],
            [
                [0]
            ],
            [
                [1]
            ],
            [
                [1, 2]
            ],
            [
                [1, 2, 3]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceLowColon
     * @param array $array
     * @param int $low
     * @param array $expected
     * @return void
     */
    public function testSliceLowColon(array $array, int $low, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$low:"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceLowColon(): array
    {
        return [
            [
                [],
                0,
                []
            ],
            [
                [],
                1,
                []
            ],
            [
                [1],
                0,
                [1]
            ],
            [
                [1],
                1,
                []
            ],
            [
                [1, 2],
                0,
                [1, 2]
            ],
            [
                [1, 2],
                1,
                [2]
            ],
            [
                [1, 2],
                2,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                [2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                [3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                7,
                [8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                [9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                9,
                []
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceNegativeLowColon
     * @param array $array
     * @param int $negativeLow
     * @param array $expected
     * @return void
     */
    public function testSliceNegativeLowColon(array $array, int $negativeLow, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$negativeLow:"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceNegativeLowColon(): array
    {
        return [
            [
                [],
                -0,
                []
            ],
            [
                [],
                -1,
                []
            ],
            [
                [],
                -2,
                []
            ],
            [
                [1],
                -0,
                [1]
            ],
            [
                [1],
                -1,
                [1]
            ],
            [
                [1],
                -2,
                [1]
            ],
            [
                [1, 2],
                -0,
                [1, 2]
            ],
            [
                [1, 2],
                -1,
                [2]
            ],
            [
                [1, 2],
                -2,
                [1, 2]
            ],
            [
                [1, 2],
                -3,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -0,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                [9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                [8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -7,
                [3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -8,
                [2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceColonHigh
     * @param array $array
     * @param int $high
     * @param array $expected
     * @return void
     */
    public function testSliceColonHigh(array $array, int $high, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView[":$high"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceColonHigh(): array
    {
        return [
            [
                [],
                0,
                []
            ],
            [
                [],
                1,
                []
            ],
            [
                [1],
                0,
                []
            ],
            [
                [1],
                1,
                [1]
            ],
            [
                [1],
                2,
                [1]
            ],
            [
                [1, 2],
                0,
                []
            ],
            [
                [1, 2],
                1,
                [1]
            ],
            [
                [1, 2],
                2,
                [1, 2]
            ],
            [
                [1, 2],
                3,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                7,
                [1, 2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                9,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                10,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceColonNegativeHigh
     * @param array $array
     * @param int $negativeHigh
     * @param array $expected
     * @return void
     */
    public function testSliceColonNegativeHigh(array $array, int $negativeHigh, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView[":$negativeHigh"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceColonNegativeHigh(): array
    {
        return [
            [
                [],
                -0,
                []
            ],
            [
                [],
                -1,
                []
            ],
            [
                [1],
                -0,
                []
            ],
            [
                [1],
                -1,
                []
            ],
            [
                [1],
                -2,
                []
            ],
            [
                [1, 2],
                -0,
                []
            ],
            [
                [1, 2],
                -1,
                [1]
            ],
            [
                [1, 2],
                -2,
                []
            ],
            [
                [1, 2],
                -3,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                [1, 2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -7,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -8,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                []
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceLowHigh
     * @param array $array
     * @param int $low
     * @param int $high
     * @param array $expected
     * @return void
     */
    public function testSliceLowHigh(array $array, int $low, int $high, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$low:$high"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceLowHigh(): array
    {
        return [
            [
                [],
                0,
                0,
                []
            ],
            [
                [],
                0,
                1,
                []
            ],
            [
                [],
                1,
                0,
                []
            ],
            [
                [],
                1,
                1,
                []
            ],
            [
                [1],
                0,
                0,
                []
            ],
            [
                [1],
                0,
                1,
                [1]
            ],
            [
                [1],
                1,
                0,
                []
            ],
            [
                [1],
                1,
                1,
                []
            ],
            [
                [1],
                1,
                2,
                []
            ],
            [
                [1, 2],
                0,
                0,
                []
            ],
            [
                [1, 2],
                0,
                1,
                [1]
            ],
            [
                [1, 2],
                0,
                2,
                [1, 2]
            ],
            [
                [1, 2],
                0,
                3,
                [1, 2]
            ],
            [
                [1, 2],
                1,
                0,
                []
            ],
            [
                [1, 2],
                1,
                1,
                []
            ],
            [
                [1, 2],
                1,
                2,
                [2]
            ],
            [
                [1, 2],
                2,
                0,
                []
            ],
            [
                [1, 2],
                2,
                1,
                []
            ],
            [
                [1, 2],
                2,
                2,
                []
            ],
            [
                [1, 2],
                2,
                3,
                []
            ],
            [
                [1, 2],
                3,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                2,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                8,
                [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                9,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                10,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                2,
                [2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                7,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                8,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                9,
                [9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceLowNegativeHigh
     * @param array $array
     * @param int $low
     * @param int $negativeHigh
     * @param array $expected
     * @return void
     */
    public function testSliceLowNegativeHigh(array $array, int $low, int $negativeHigh, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$low:$negativeHigh"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceLowNegativeHigh(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                -1,
                [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                -2,
                [1, 2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                -7,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                -8,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                -9,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                -1,
                [2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                -2,
                [2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                -1,
                [3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                -2,
                [3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                -6,
                [3]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                -7,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                7,
                -1,
                [8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                -1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                9,
                -1,
                []
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceNegativeLowHigh
     * @param array $array
     * @param int $negativeLow
     * @param int $high
     * @param array $expected
     * @return void
     */
    public function testSliceNegativeLowHigh(array $array, int $negativeLow, int $high, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$negativeLow:$high"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceNegativeLowHigh(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                2,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                9,
                [9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                7,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                8,
                [8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                9,
                [8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                10,
                [8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                2,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                9,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                0,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                2,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                9,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceNegativeLowNegativeHigh
     * @param array $array
     * @param int $negativeLow
     * @param int $negativeHigh
     * @param array $expected
     * @return void
     */
    public function testSliceNegativeLowNegativeHigh(
        array $array,
        int $negativeLow,
        int $negativeHigh,
        array $expected
    ): void {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$negativeLow:$negativeHigh"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceNegativeLowNegativeHigh(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                -1,
                [1, 2, 3, 4, 5, 6, 7, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                -2,
                [1, 2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                -8,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                -9,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -7,
                -3,
                [3, 4, 5, 6]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -3,
                -7,
                []
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceDoubleColonStep
     * @param array $array
     * @param int $step
     * @param array $expected
     * @return void
     */
    public function testSliceDoubleColonStep(array $array, int $step, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["::$step"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceDoubleColonStep(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                [1, 3, 5, 7, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                3,
                [1, 4, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                4,
                [1, 5, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                5,
                [1, 6]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                6,
                [1, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                7,
                [1, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                [1, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                9,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                10,
                [1]
            ],
        ];
    }

    public function testSliceDoubleColonStepZeroError(): void
    {
        // Given
        $array = [0, 1, 2, 3, 4];
        $arrayView = ArrayView::toView($array);

        // Then
        $this->expectException(IndexError::class);
        $this->expectExceptionMessage('Step cannot be 0');

        // When
        $slice = $arrayView['::0'];
    }

    /**
     * @dataProvider dataProviderForSliceDoubleColonNegativeStep
     * @param array $array
     * @param int $negativeStep
     * @param array $expected
     * @return void
     */
    public function testSliceDoubleColonNegativeStep(array $array, int $negativeStep, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["::$negativeStep"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceDoubleColonNegativeStep(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -1,
                [9, 8, 7, 6, 5, 4, 3, 2, 1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                [9, 7, 5, 3, 1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -3,
                [9, 6, 3]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -4,
                [9, 5, 1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -5,
                [9, 4]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -6,
                [9, 3]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -7,
                [9, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -8,
                [9, 1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -9,
                [9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -10,
                [9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceLowDoubleColonStep
     * @param array $array
     * @param int $low
     * @param int $step
     * @param array $expected
     * @return void
     */
    public function testSliceLowDoubleColonStep(array $array, int $low, int $step, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$low::$step"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceLowDoubleColonStep(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                2,
                [1, 3, 5, 7, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                1,
                [2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                2,
                [2, 4, 6, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                2,
                [3, 5, 7, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                2,
                [8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                -2,
                [3, 1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                -2,
                [8, 6, 4, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                [1, 2, 3, 4, 5, 6, 7, 8, 9]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceColonHighStep
     * @param array $array
     * @param int $high
     * @param int $step
     * @param array $expected
     * @return void
     */
    public function testSliceColonHighStep(array $array, int $high, int $step, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView[":$high:$step"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceColonHighStep(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                1,
                [1, 2]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                8,
                3,
                [1, 4, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -8,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                1,
                [1, 2, 3, 4, 5, 6, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -2,
                3,
                [1, 4, 7]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -5,
                -2,
                [9, 7]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForSliceLowHighStep
     * @param array $array
     * @param int $low
     * @param int $high
     * @param int $step
     * @param array $expected
     * @return void
     */
    public function testSliceLowHighStepStep(array $array, int $low, int $high, int $step, array $expected): void
    {
        // Given
        $arrayView = ArrayView::toView($array);

        // When
        $slice = $arrayView["$low:$high:$step"];

        // Then
        $this->assertSame($expected, $slice);
    }

    public static function dataProviderForSliceLowHighStep(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                0,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                0,
                1,
                1,
                [1]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                0,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                1,
                1,
                1,
                []
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                2,
                6,
                2,
                [3, 5]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -6,
                8,
                2,
                [4, 6, 8]
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                -4,
                -2,
                1,
                [6, 7]
            ],
        ];
    }
}
