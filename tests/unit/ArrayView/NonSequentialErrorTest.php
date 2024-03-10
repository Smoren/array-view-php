<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Views\ArrayView;

class NonSequentialErrorTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForNonSequentialError
     */
    public function testNonSequentialError(callable $arrayGetter)
    {
        $nonSequentialArray = $arrayGetter();
        $this->expectException(ValueError::class);
        ArrayView::toView($nonSequentialArray);
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
