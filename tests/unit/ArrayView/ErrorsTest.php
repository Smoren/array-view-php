<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\NotSupportedError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

class ErrorsTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForOutOfRangeIndexes
     */
    public function testReadIndexError(array $source, array $indexes)
    {
        $view = ArrayView::toView($source);
        foreach ($indexes as $index) {
            try {
                $_ = $view[$index];
                $strIndex = strval($index);
                $this->fail("IndexError not thrown for key: \"{$strIndex}\"");
            } catch (IndexError $e) {
                $this->assertSame("Index {$index} is out of range.", $e->getMessage());
            }
        }
    }

    /**
     * @dataProvider dataProviderForOutOfRangeIndexes
     */
    public function testWriteIndexError(array $source, array $indexes)
    {
        $view = ArrayView::toView($source);
        foreach ($indexes as $index) {
            try {
                $view[$index] = 1;
                $strIndex = strval($index);
                $this->fail("IndexError not thrown for key: \"{$strIndex}\"");
            } catch (IndexError $e) {
                $this->assertSame("Index {$index} is out of range.", $e->getMessage());
            }
        }
    }

    /**
     * @dataProvider dataProviderForBadKeys
     */
    public function testReadKeyError(array $source, array $keys)
    {
        $view = ArrayView::toView($source);
        foreach ($keys as $key) {
            $strKey = is_scalar($key) ? strval($key) : gettype($key);
            try {
                $_ = $view[$key];
                $this->fail("KeyError not thrown for key: \"{$strKey}\"");
            } catch (KeyError $e) {
                $this->assertSame("Invalid key: \"{$strKey}\".", $e->getMessage());
            }
        }
    }

    /**
     * @dataProvider dataProviderForBadKeys
     */
    public function testWriteKeyError(array $source, array $keys)
    {
        $view = ArrayView::toView($source);
        foreach ($keys as $key) {
            $strKey = is_scalar($key) ? strval($key) : gettype($key);
            try {
                $view[$key] = 1;
                $this->fail("KeyError not thrown for key: \"{$strKey}\"");
            } catch (KeyError $e) {
                $this->assertSame("Invalid key: \"{$strKey}\".", $e->getMessage());
            }
        }
    }

    /**
     * @dataProvider dataProviderForBadSizeMask
     */
    public function testReadByMaskSizeError(array $source, array $boolMask)
    {
        $view = ArrayView::toView($source);

        $boolMaskSize = \count($boolMask);
        $sourceSize = \count($source);

        $this->expectException(SizeError::class);
        $this->expectExceptionMessage("Mask size not equal to source length ({$boolMaskSize} != {$sourceSize}).");

        $_ = $view[new MaskSelector($boolMask)];
    }

    /**
     * @dataProvider dataProviderForBadSizeMask
     */
    public function testGetSubviewByMaskSizeError(array $source, array $boolMask)
    {
        $view = ArrayView::toView($source);

        $boolMaskSize = \count($boolMask);
        $sourceSize = \count($source);

        $this->expectException(SizeError::class);
        $this->expectExceptionMessage("Mask size not equal to source length ({$boolMaskSize} != {$sourceSize}).");

        $view->subview(new MaskSelector($boolMask));
    }

    /**
     * @dataProvider dataProviderForBadSizeMask
     */
    public function testWriteByMaskSizeError(array $source, array $boolMask)
    {
        $view = ArrayView::toView($source);

        $boolMaskSize = \count($boolMask);
        $sourceSize = \count($source);

        $this->expectException(SizeError::class);
        $this->expectExceptionMessage("Mask size not equal to source length ({$boolMaskSize} != {$sourceSize}).");

        $view[new MaskSelector($boolMask)] = $boolMask;
    }

    /**
     * @dataProvider dataProviderForInvalidSlice
     */
    public function testInvalidSliceRead(array $source, string $slice)
    {
        $view = ArrayView::toView($source);

        $this->expectException(IndexError::class);
        $this->expectExceptionMessage("Step cannot be 0.");

        $_ = $view[new SliceSelector($slice)];
    }

    /**
     * @dataProvider dataProviderForInvalidSlice
     */
    public function testInvalidSliceWrite(array $source, string $slice)
    {
        $view = ArrayView::toView($source);

        $this->expectException(IndexError::class);
        $this->expectExceptionMessage("Step cannot be 0.");

        $view[new SliceSelector($slice)] = [1, 2, 3];
    }

    /**
     * @dataProvider dataProviderForWriteSizeError
     */
    public function testWriteSizeError(array $source, callable $viewGetter, array $toWrite)
    {
        $view = ArrayView::toView($source);

        $sourceSize = \count($source);
        $argSize = \count($toWrite);

        $this->expectException(SizeError::class);
        $this->expectExceptionMessage("Length of values array not equal to view length ({$argSize} != {$sourceSize}).");

        $view[':'] = $toWrite;
    }

    /**
     * @dataProvider dataProviderForUnsetError
     */
    public function testUnsetError(array $source, $index)
    {
        $view = ArrayView::toView($source);

        $this->expectException(NotSupportedError::class);

        unset($view[$index]);
    }

    /**
     * @dataProvider dataProviderForNonSequentialError
     */
    public function testNonSequentialError(callable $arrayGetter)
    {
        $nonSequentialArray = $arrayGetter();
        $this->expectException(ValueError::class);
        ArrayView::toView($nonSequentialArray);
    }

    /**
     * @dataProvider dataProviderForBadIndexList
     */
    public function testReadBadIndexList(array $source, array $indexes)
    {
        $view = ArrayView::toView($source);
        $this->expectException(IndexError::class);
        $this->expectExceptionMessage('Some indexes are out of range.');
        $_ = $view[new IndexListSelector($indexes)];
    }

    /**
     * @dataProvider dataProviderForBadIndexList
     */
    public function testWriteBadIndexList(array $source, array $indexes)
    {
        $initialSource = [...$source];
        $view = ArrayView::toView($source);

        try {
            $view[new IndexListSelector($indexes)] = $indexes;
        } catch (IndexError $e) {
            $this->assertSame($initialSource, [...$view]);
            $this->assertSame('Some indexes are out of range.', $e->getMessage());
        }
    }

    /**
     * @dataProvider dataProviderForSequentialError
     */
    public function testMapWithSequentialError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->mapWith($arg, fn ($lhs, $rhs) => $lhs + $rhs);
        } catch (ValueError $e) {
            $this->assertSame('Argument is not sequential.', $e->getMessage());
        }
    }

    /**
     * @dataProvider dataProviderForSizeError
     */
    public function testMapWithSizeError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->mapWith($arg, fn ($lhs, $rhs) => $lhs + $rhs);
        } catch (SizeError $e) {
            [$lhsSize, $rhsSize] = array_map('count', [$arg, $source]);
            $this->assertSame(
                "Length of values array not equal to view length ({$lhsSize} != {$rhsSize}).",
                $e->getMessage()
            );
        }
    }

    /**
     * @dataProvider dataProviderForSequentialError
     */
    public function testMatchWithSequentialError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->matchWith($arg, fn ($lhs, $rhs) => $lhs && $rhs);
        } catch (ValueError $e) {
            $this->assertSame('Argument is not sequential.', $e->getMessage());
        }
    }

    /**
     * @dataProvider dataProviderForSizeError
     */
    public function testMatchWithSizeError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->matchWith($arg, fn ($lhs, $rhs) => $lhs && $rhs);
        } catch (SizeError $e) {
            [$lhsSize, $rhsSize] = array_map('count', [$arg, $source]);
            $this->assertSame(
                "Length of values array not equal to view length ({$lhsSize} != {$rhsSize}).",
                $e->getMessage()
            );
        }
    }

    /**
     * @dataProvider dataProviderForSequentialError
     */
    public function testApplyWithSequentialError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->applyWith($arg, fn ($lhs, $rhs) => $lhs + $rhs);
        } catch (ValueError $e) {
            $this->assertSame('Argument is not sequential.', $e->getMessage());
        }
    }

    /**
     * @dataProvider dataProviderForSizeError
     */
    public function testApplyWithSizeError(array $source, array $arg)
    {
        $view = ArrayView::toView($source);

        try {
            $view->applyWith($arg, fn ($lhs, $rhs) => $lhs && $rhs);
        } catch (SizeError $e) {
            [$lhsSize, $rhsSize] = array_map('count', [$arg, $source]);
            $this->assertSame(
                "Length of values array not equal to view length ({$lhsSize} != {$rhsSize}).",
                $e->getMessage()
            );
        }
    }

    public function dataProviderForOutOfRangeIndexes(): array
    {
        return [
            [[], [-2, -1, 0, 1, NAN, INF, -INF]],
            [[1], [-3, -2, 1, 2, NAN, INF, -INF]],
            [[1, 2, 3], [-100, -5, 4, 100, NAN, INF, -INF]],
        ];
    }

    public function dataProviderForBadKeys(): array
    {
        return [
            [[], ['a', 'b', 'c']],
            [[], ['1a', 'test', '!']],
            [[], [['a' => 'test']], new \stdClass([])],
            [[], [null, true, false, ['a' => 'test']], new \stdClass([])],
            [[1], ['a', 'b', 'c']],
            [[1], ['1a', 'test', '!']],
            [[1], [null, true, false, ['a' => 'test']], new \stdClass([])],
            [[1, 2, 3], ['a', 'b', 'c']],
            [[1, 2, 3], ['1a', 'test', '!']],
            [[2], [null, true, false, ['a' => 'test']], new \stdClass([])],
        ];
    }

    public function dataProviderForBadSizeMask(): array
    {
        return [
            [[], [1]],
            [[1], []],
            [[1], [1, 0]],
            [[1, 2, 3], [1]],
            [[1, 2, 3], [0]],
            [[1, 2, 3], [0, 1]],
            [[1, 2, 3], [0, 1, 1, 0]],
            [[1, 2, 3], [1, 1, 1, 1, 1]],
            [[1, 2, 3], [0, 0, 0, 0, 0]],
        ];
    }

    public function dataProviderForInvalidSlice(): array
    {
        return [
            [[], '::0'],
            [[], '0:0:0'],
            [[], '0:1:0'],
            [[], '0::0'],
            [[], ':-1:0'],
            [[], '1:-1:0'],
            [[1], '::0'],
            [[1], '0:0:0'],
            [[1], '0:1:0'],
            [[1], '0::0'],
            [[1], ':-1:0'],
            [[1], '1:-1:0'],
            [[1, 2, 3], '::0'],
            [[1, 2, 3], '0:0:0'],
            [[1, 2, 3], '0:1:0'],
            [[1, 2, 3], '0::0'],
            [[1, 2, 3], ':-1:0'],
            [[1, 2, 3], '1:-1:0'],
        ];
    }

    public function dataProviderForWriteSizeError(): array
    {
        return [
            [
                [],
                fn (array &$source) => ArrayView::toView($source),
                [1],
            ],
            [
                [1],
                fn (array &$source) => ArrayView::toView($source),
                [],
            ],
            [
                [1],
                fn (array &$source) => ArrayView::toView($source),
                [1, 2],
            ],
            [
                [1, 2, 3],
                fn (array &$source) => ArrayView::toView($source),
                [1, 2],
            ],
            [
                [1, 2, 3],
                fn (array &$source) => ArrayView::toView($source),
                [1, 2, 3, 4, 5],
            ],
        ];
    }

    public function dataProviderForUnsetError(): array
    {
        return [
            [[], 0],
            [[], 1],
            [[], -1],
            [[], null],
            [[], true],
            [[], false],
            [[], []],
            [[], [1, 2, 3]],
            [[], ['test' => 123]],
            [[], new \ArrayObject([1, 2, 3])],
            [[], INF],
            [[], -INF],
            [[], NAN],
            [[], new SliceSelector('0:2')],
            [[], new MaskSelector([0, 1])],
            [[], new IndexListSelector([0, 1])],
            [[1, 2, 3], 0],
            [[1, 2, 3], 1],
            [[1, 2, 3], -1],
            [[1, 2, 3], null],
            [[1, 2, 3], true],
            [[1, 2, 3], false],
            [[1, 2, 3], []],
            [[1, 2, 3], [1, 2, 3]],
            [[1, 2, 3], ['test' => 123]],
            [[1, 2, 3], new \ArrayObject([1, 2, 3])],
            [[1, 2, 3], INF],
            [[1, 2, 3], -INF],
            [[1, 2, 3], NAN],
            [[1, 2, 3], new SliceSelector('0:2')],
            [[1, 2, 3], new MaskSelector([0, 1])],
            [[1, 2, 3], new IndexListSelector([0, 1])],
        ];
    }

    public function dataProviderForBadIndexList(): array
    {
        return [
            [[1], [0, 1]],
            [[1], [1, -1, -2]],
            [[1], [0, 1, 0, -1, -2]],
            [[1], [1, -1]],
            [[1], [0, 0, -2]],
            [[1, 2], [2]],
            [[1, 2], [1, 2]],
            [[1, 2], [0, 1, 2]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 3, 5, -10]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [9, 5, 3, 1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 10, 9, 7]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [-10, 1, 7, 10]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 1, 50, 5, 3]],
        ];
    }

    public function dataProviderForNonSequentialError(): array
    {
        return [
            [fn () => ['test' => 1]],
            [fn () => [1 => 1]],
            [fn () => [0 => 1, 2 => 2]],
            [fn () => [0 => 1, -1 => 2]],
            [fn () => [0 => 1, 'a' => 2]],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                unset($array[0]);
                return $array;
            }],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                unset($array[1]);
                return $array;
            }],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                $array[6] = 111;
                return $array;
            }],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                $array[-1] = 111;
                return $array;
            }],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                $array[-2] = 111;
                return $array;
            }],
            [static function () {
                $array = [1, 2, 3, 4, 5];
                $array['test'] = 111;
                return $array;
            }],
        ];
    }

    public function dataProviderForSequentialError(): array
    {
        return [
            [[], ['test' => 123]],
            [[], [1 => 1]],
            [[], [0, 2 => 1]],
            [[1, 2, 3], ['test' => 123]],
            [[1, 2, 3], [1 => 1]],
            [[1, 2, 3], [0, 2 => 1]],
        ];
    }

    public function dataProviderForSizeError(): array
    {
        return [
            [[], [1]],
            [[], [1, 2, 3]],
            [[1, 2, 3], []],
            [[1, 2, 3], [1, 2]],
            [[1, 2, 3], [1, 2, 3, 4]],
        ];
    }
}
