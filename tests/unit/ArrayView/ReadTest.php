<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayIndexListView;
use Smoren\ArrayView\Views\ArrayMaskView;
use Smoren\ArrayView\Views\ArraySliceView;
use Smoren\ArrayView\Views\ArrayView;

class ReadTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArrayRead
     */
    public function testRead(array $source)
    {
        $view = ArrayView::toView($source);

        foreach ($source as $i => $value) {
            $actual = $view[$i];
            $actualByStringIndex = $view[strval($i)];
            $expected = $source[$i];

            $this->assertSame($expected, $actual);
            $this->assertSame($expected, $actualByStringIndex);
        }

        $this->assertSame($source, $view->toArray());
        $this->assertSame($source, [...$view]);
    }

    /**
     * @dataProvider dataProviderForReadCombine
     */
    public function testReadCombined(array $source, callable $viewGetter, array $expected)
    {
        $view = $viewGetter($source);

        $this->assertSame($view->toArray(), $expected);
    }

    public function dataProviderForArrayRead(): array
    {
        return [
            [[1]],
            [[1, 2]],
            [[1, 2, 3]],
            [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
            [[10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10]],
        ];
    }

    public function dataProviderForReadCombine(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2'),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => (new ArrayIndexListView($source, array_keys($source)))
                    ->subview('::2'),
                [1, 3, 5, 7, 9],
            ],

            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => (new ArrayIndexListView($source, array_keys($source)))
                    ->subview('::2'),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => (new ArrayMaskView(
                    $source,
                    [true, true, true, true, true, true, true, true, true, true]
                ))->subview('::2'),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => (new ArraySliceView($source, new SliceSelector("::1")))
                    ->subview('::2'),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true])),
                [1, 5, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector(ArrayView::toUnlinkedView([true, false, true, false, true])))
                    ->subview(new IndexListSelector(ArrayView::toUnlinkedView([0, 2]))),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview('1:'),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true]))
                    ->subview(new MaskSelector([false, true])),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new IndexListSelector([0, 2, 4, 6, 8]))
                    ->subview(new IndexListSelector([0, 2, 4]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview(new IndexListSelector([1])),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2'),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('1:'),
                [9],
            ],
        ];
    }
}
