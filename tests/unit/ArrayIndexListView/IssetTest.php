<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayIndexListView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayIndexListView;
use Smoren\ArrayView\Views\ArrayView;

class IssetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForIssetTrue
     */
    public function testIssetTrue(array $source, array $indexes)
    {
        $view = ArrayView::toView($source);
        $subview = $view->subview(new IndexListSelector($indexes));

        $existIndexes = [
            ...range(0, \count($indexes) - 1),
            ...range(-1, -\count($indexes)),
        ];

        foreach ($existIndexes as $index) {
            $this->assertTrue(isset($subview[$index]), $index);
        }
    }

    /**
     * @dataProvider dataProviderForIssetFalse
     */
    public function testIssetFalse(array $source, array $indexes, array $expected)
    {
        $view = ArrayView::toView($source);
        $subview = $view->subview(new IndexListSelector($indexes));

        foreach ($expected as $index) {
            $this->assertFalse(isset($subview[$index]), $index);
        }
    }

    public function dataProviderForIssetTrue(): array
    {
        return [
            [[1], [0]],
            [[1], [0, 0]],
            [[1], [0, 0, 0]],
            [[1, 2], [0]],
            [[1, 2], [1]],
            [[1, 2], [0, 1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 3, 5, 7]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [7, 5, 3, 1]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 5, 3, 7]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [0, 1, 7, 8]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 1, 5, 5, 3]],
        ];
    }

    public static function dataProviderForIssetFalse(): array
    {
        return [
            [[], [], [-2, -1, 0, 1, 2]],
            [[1], [], [-2, -1, 0, 1, 2]],
            [[1, 2, 3], [], [-2, -1, 0, 1, 2]],
            [[1], [0], [-3, -2, 1, 2]],
            [[1], [0, 0], [-5, -4, -3, 2, 3, 4]],
            [[1], [0, 0, 0], [-6, -5, -4, 3, 4, 5]],
            [[1, 2], [0], [-3, -2, 1, 2]],
            [[1, 2], [1], [-3, -2, 1, 2]],
            [[1, 2], [0, 1], [-5, -4, -3, 2, 3, 4]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 3, 5, 7], [-7, -6, -5, 4, 5, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [7, 5, 3, 1], [-7, -6, -5, 4, 5, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 5, 3, 7], [-7, -6, -5, 4, 5, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [0, 1, 7, 8], [-7, -6, -5, 4, 5, 6]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], [1, 1, 5, 5, 3], [-8, -7, -6, 5, 6, 7]],
        ];
    }
}
