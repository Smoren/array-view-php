<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\KeyError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Selectors\MaskSelector;
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
     * @dataProvider dataProviderForNonSequentialError
     */
    public function testNonSequentialError(callable $arrayGetter)
    {
        $nonSequentialArray = $arrayGetter();
        $this->expectException(ValueError::class);
        ArrayView::toView($nonSequentialArray);
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
            [[], [[], [1, 2, 3], ['a' => 'test']], new \stdClass([])],
            [[], [null, true, false, [], [1, 2, 3], ['a' => 'test']], new \stdClass([])],
            [[1], ['a', 'b', 'c']],
            [[1], ['1a', 'test', '!']],
            [[1], [null, true, false, [], [1, 2, 3], ['a' => 'test']], new \stdClass([])],
            [[1, 2, 3], ['a', 'b', 'c']],
            [[1, 2, 3], ['1a', 'test', '!']],
            [[2], [null, true, false, [], [1, 2, 3], ['a' => 'test']], new \stdClass([])],
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
}
