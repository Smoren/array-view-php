<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\PipeSelector;
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

    /**
     * @dataProvider dataProviderForReadPipe
     */
    public function testReadPipe(array $source, array $selectors, array $expected)
    {
        $view = ArrayView::toView($source);
        $selector = new PipeSelector($selectors);

        $subview = $view->subview($selector);
        $subArray = $view[$selector];

        $this->assertSame($subview->toArray(), $expected);
        $this->assertSame($subArray, $expected);
        $this->assertSame($selector->getValue(), $selectors);
    }

    /**
     * @dataProvider dataProviderForMatchAndFilter
     */
    public function testMatchAndFilter(array $source, callable $predicate, array $expectedMask, array $expectedArray)
    {
        // Given
        $view = ArrayView::toView($source);

        // When
        $boolMask = $view->is($predicate);
        $boolMaskCopy = $view->match($predicate);
        $filtered = $view->filter($predicate);

        // Then
        $this->assertSame($expectedMask, $boolMask->getValue());
        $this->assertSame($expectedMask, $boolMaskCopy->getValue());
        $this->assertSame($expectedArray, $view->subview($boolMask)->toArray());
        $this->assertSame($expectedArray, $filtered->toArray());
    }

    /**
     * @dataProvider dataProviderForMatchWith
     */
    public function testMatchWith(
        array $source,
        array $another,
        callable $comparator,
        array $expectedMask,
        array $expectedArray
    ) {
        // Given
        $view = ArrayView::toView($source);

        // When
        $boolMask = $view->matchWith($another, $comparator);

        // Then
        $this->assertSame($expectedMask, $boolMask->getValue());
        $this->assertSame($expectedArray, $view->subview($boolMask)->toArray());
    }

    /**
     * @dataProvider dataProviderForMap
     */
    public function testMap(
        array $source,
        callable $mapper,
        array $expected
    ) {
        // Given
        $view = ArrayView::toView($source);

        // When
        $actual = $view->map($mapper);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForMapWith
     */
    public function testMapWith(array $source, $another, callable $mapper, array $expected)
    {
        // Given
        $view = ArrayView::toView($source);

        // When
        $actual = $view->mapWith($another, $mapper);

        // Then
        $this->assertSame($expected, $actual);
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
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new PipeSelector([
                        new SliceSelector('::2'),
                        new MaskSelector([true, false, true, false, true]),
                    ]))
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector('::2'))
                    ->subview(new PipeSelector([
                        new MaskSelector([true, false, true, false, true]),
                    ]))
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector('::2'))
                    ->subview(new PipeSelector([
                        new MaskSelector([true, false, true, false, true]),
                        new IndexListSelector([0, 2])
                    ])),
                [1, 9],
            ],
        ];
    }

    public function dataProviderForReadPipe(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [new SliceSelector('::2')],
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [new SliceSelector('::2')],
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [new SliceSelector('::2')],
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new MaskSelector([true, true, true, true, true, true, true, true, true, true]),
                    new SliceSelector('::2'),
                ],
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::1'),
                    new SliceSelector('::2'),
                ],
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new MaskSelector([true, false, true, false, true]),
                ],
                [1, 5, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new MaskSelector([true, false, true, false, true]),
                    new IndexListSelector([0, 2]),
                ],
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new MaskSelector([true, false, true, false, true]),
                    new IndexListSelector([0, 2]),
                    new SliceSelector('1:'),
                ],
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new MaskSelector([true, false, true, false, true, false, true, false, true, false]),
                    new MaskSelector([true, false, true, false, true]),
                    new MaskSelector([true, false, true]),
                    new MaskSelector([false, true]),
                ],
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new MaskSelector([true, false, true, false, true, false, true, false, true, false]),
                    new MaskSelector([true, false, true, false, true]),
                    new MaskSelector([true, false, true]),
                ],
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new IndexListSelector([0, 2, 4, 6, 8]),
                    new IndexListSelector([0, 2, 4]),
                    new IndexListSelector([0, 2]),
                    new IndexListSelector([1]),
                ],
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new SliceSelector('::2'),
                    new SliceSelector('::2'),
                ],
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new SliceSelector('::2'),
                    new SliceSelector('::2'),
                    new SliceSelector('1:'),
                ],
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new PipeSelector([
                        new SliceSelector('::2'),
                        new SliceSelector('::2'),
                    ]),
                    new SliceSelector('1:'),
                ],
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [
                    new SliceSelector('::2'),
                    new PipeSelector([
                        new PipeSelector([
                            new SliceSelector('::2'),
                            new SliceSelector('::2'),
                        ]),
                    ]),
                    new SliceSelector('1:'),
                ],
                [9],
            ],
        ];
    }

    public function dataProviderForMatchAndFilter(): array
    {
        return [
            [
                [],
                fn (int $x) => $x % 2 === 0,
                [],
                [],
            ],
            [
                [1],
                fn (int $x) => $x % 2 === 0,
                [false],
                [],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (int $x) => $x % 2 === 0,
                [false, true, false, true, false, true, false, true, false, true],
                [2, 4, 6, 8, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (int $_, int $i) => $i % 2 === 0,
                [true, false, true, false, true, false, true, false, true, false],
                [1, 3, 5, 7, 9],
            ],
        ];
    }

    public function dataProviderForMatchWith(): array
    {
        return [
            [
                [],
                [],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [],
                [],
            ],
            [
                [1],
                [1],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [false],
                [],
            ],
            [
                [1],
                [2],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [true],
                [1],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 22, 3, 4, 5, 6, 7, 8, 99, 10],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [false, true, false, false, false, false, false, false, true, false],
                [2, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 22, 3, 4, 5, 6, 7, 8, 99, 10],
                fn (int $lhs, int $rhs) => $lhs >= $rhs,
                [true, false, true, true, true, true, true, true, false, true],
                [1, 3, 4, 5, 6, 7, 8, 10],
            ],
        ];
    }

    public function dataProviderForMap(): array
    {
        return [
            [
                [],
                fn (int $x) => $x,
                [],
            ],
            [
                [1],
                fn (int $x) => $x,
                [1],
            ],
            [
                [1],
                fn (int $x) => $x * 2,
                [2],
            ],
            [
                [1, 2, 3],
                fn (int $x) => $x + 1,
                [2, 3, 4],
            ],
            [
                [1, 2, 3],
                fn (int $x) => [$x + 1],
                [[2], [3], [4]],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (int $_, int $i) => $i % 2 === 0,
                [true, false, true, false, true, false, true, false, true, false],
            ],
        ];
    }

    public function dataProviderForMapWith(): array
    {
        return [
            [
                [],
                [],
                fn (int $lhs, int $rhs) => $rhs + $lhs,
                [],
            ],
            [
                [1],
                [2],
                fn (int $lhs, int $rhs) => $rhs + $lhs,
                [3],
            ],
            [
                [1],
                [2],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [true],
            ],
            [
                [1, 2, 3],
                [10, 20, 30],
                fn (int $lhs, int $rhs) => $rhs + $lhs,
                [11, 22, 33],
            ],
            [
                [1, 2, 3],
                [10, 20, 30],
                fn (int $lhs, int $rhs) => [$lhs, $rhs],
                [[1, 10], [2, 20], [3, 30]],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                [1, 22, 3, 4, 5, 6, 7, 8, 99, 10],
                fn (int $lhs, int $rhs) => $rhs > $lhs,
                [false, true, false, false, false, false, false, false, true, false],
            ],
            [
                [1, 2, 3],
                10,
                fn (int $lhs, int $rhs) => $rhs + $lhs,
                [11, 12, 13],
            ],
            [
                [1, 2, 3],
                ArrayView::toUnlinkedView([10, 20, 30]),
                fn (int $lhs, int $rhs) => [$lhs, $rhs],
                [[1, 10], [2, 20], [3, 30]],
            ],
        ];
    }
}
