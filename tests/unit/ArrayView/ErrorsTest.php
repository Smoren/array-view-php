<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;
use Smoren\ArrayView\Exceptions\ValueError;
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
                $this->fail();
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
                $this->fail();
            } catch (IndexError $e) {
                $this->assertSame("Index {$index} is out of range.", $e->getMessage());
            }
        }
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
            [[], [-2, -1, 0, 1]],
            [[1], [-3, -2, 1, 2]],
            [[1, 2, 3], [-100, -5, 4, 100]],
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
