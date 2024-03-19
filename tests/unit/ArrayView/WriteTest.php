<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\PipeSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Views\ArrayView;

class WriteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArrayWrite
     */
    public function testWriteByIndex(array $source, array $toWrite)
    {
        $view = ArrayView::toView($source);

        foreach ($source as $i => $value) {
            $view[$i] = $toWrite[$i];

            $this->assertSame($toWrite[$i], $view[$i]);
            $this->assertSame($toWrite[$i], $source[$i]);
        }

        $this->assertSame($toWrite, $view->toArray());
        $this->assertSame($toWrite, [...$view]);
        $this->assertSame($toWrite, $source);
    }

    /**
     * @dataProvider dataProviderForArrayWrite
     */
    public function testWriteArrayBySet(array $source, array $toWrite)
    {
        $view = ArrayView::toView($source);

        $view->set($toWrite);

        $this->assertSame($toWrite, $view->toArray());
        $this->assertSame($toWrite, [...$view]);
        $this->assertSame($toWrite, $source);
    }

    /**
     * @dataProvider dataProviderForArrayWrite
     */
    public function testWriteArrayBySlice(array $source, array $toWrite)
    {
        $view = ArrayView::toView($source);

        $view[':'] = $toWrite;

        $this->assertSame($toWrite, $view->toArray());
        $this->assertSame($toWrite, [...$view]);
        $this->assertSame($toWrite, $source);
    }

    /**
     * @dataProvider dataProviderForSingleWrite
     */
    public function testWriteSingleBySet(array $source, $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view->set($toWrite);

        $this->assertSame($expected, $view->toArray());
        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForSingleWrite
     */
    public function testWriteSingleBySlice(array $source, $toWrite, array $expected)
    {
        $view = ArrayView::toView($source);

        $view[':'] = $toWrite;

        $this->assertSame($expected, $view->toArray());
        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForIncrement
     */
    public function testIncrement(array $source, array $expected)
    {
        $view = ArrayView::toView($source);

        foreach ($source as $i => $value) {
            $view[$i] += 1;

            $this->assertSame($expected[$i], $source[$i]);
            $this->assertSame($expected[$i], $view[$i]);
        }

        $this->assertSame($expected, $view->toArray());
        $this->assertSame($expected, [...$view]);
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForWriteCombine
     * @dataProvider dataProviderForWritePipe
     */
    public function testWriteBySet(array $source, callable $viewGetter, $toWrite, array $expected)
    {
        $view = $viewGetter($source);

        $view->set($toWrite);

        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForWriteCombine
     * @dataProvider dataProviderForWritePipe
     */
    public function testWriteBySlice(array $source, callable $viewGetter, $toWrite, array $expected)
    {
        $view = $viewGetter($source);

        $view[':'] = $toWrite;

        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForApply
     */
    public function testApply(array $source, callable $viewGetter, callable $mapper, array $expected)
    {
        // Given
        $view = $viewGetter($source);

        // When
        $view->apply($mapper);

        // Then
        $this->assertSame($expected, $source);
    }

    /**
     * @dataProvider dataProviderForApplyWith
     */
    public function testApplyWith(array $source, callable $viewGetter, callable $mapper, $arg, array $expected)
    {
        // Given
        $view = $viewGetter($source);

        // When
        $view->applyWith($arg, $mapper);

        // Then
        $this->assertSame($expected, $source);
    }

    public function dataProviderForArrayWrite(): array
    {
        return [
            [[1], [0]],
            [[1, 2], [3, 5]],
            [[1, 2, 3], [11, 22, 33]],
        ];
    }

    public function dataProviderForSingleWrite(): array
    {
        return [
            [[1], 1, [1]],
            [[1, 2], 2, [2, 2]],
            [[1, 2, 3], 33, [33, 33, 33]],
        ];
    }

    public function dataProviderForIncrement(): array
    {
        return [
            [[1], [2]],
            [[1, 2], [2, 3]],
            [[3, 2, 1], [4, 3, 2]],
        ];
    }

    public function dataProviderForWriteCombine(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2'),
                [11, 33, 55, 77, 99],
                [11, 2, 33, 4, 55, 6, 77, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true])),
                [11, 55, 99],
                [11, 2, 3, 4, 55, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2])),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview('1:'),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true]))
                    ->subview(new MaskSelector([false, true])),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new MaskSelector(ArrayView::toUnlinkedView(
                        [true, false, true, false, true, false, true, false, true, false]
                    )))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true])),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new IndexListSelector([0, 2, 4, 6, 8]))
                    ->subview(new IndexListSelector([0, 2, 4]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview(new IndexListSelector([1])),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('1:'),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector(new Slice(null, null, 2)))
                    ->subview(new SliceSelector('::2'))
                    ->subview('::2'),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector(new Slice(null, null, 2)))
                    ->subview(new SliceSelector('::2'))
                    ->subview('::2'),
                111,
                [111, 2, 3, 4, 5, 6, 7, 8, 111, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new PipeSelector([
                        new SliceSelector('::2'),
                        new MaskSelector([true, false, true, false, true]),
                    ]))
                    ->subview(new IndexListSelector([0, 2])),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector('::2'))
                    ->subview(new PipeSelector([
                        new MaskSelector([true, false, true, false, true]),
                    ]))
                    ->subview(new IndexListSelector([0, 2])),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview(new SliceSelector('::2'))
                    ->subview(new PipeSelector([
                        new MaskSelector([true, false, true, false, true]),
                        new IndexListSelector([0, 2]),
                    ])),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
        ];
    }

    public function dataProviderForWritePipe(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector('::2'),
                    ])
                ),
                [11, 33, 55, 77, 99],
                [11, 2, 33, 4, 55, 6, 77, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector('::2'),
                        new MaskSelector([true, false, true, false, true]),
                    ])
                ),
                [11, 55, 99],
                [11, 2, 3, 4, 55, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector('::2'),
                        new MaskSelector([true, false, true, false, true]),
                        new IndexListSelector([0, 2]),
                    ])
                ),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector('::2'),
                        new MaskSelector([true, false, true, false, true]),
                        new IndexListSelector([0, 2]),
                        new SliceSelector('1:'),
                    ])
                ),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new MaskSelector([true, false, true, false, true, false, true, false, true, false]),
                        new MaskSelector([true, false, true, false, true]),
                        new MaskSelector([true, false, true]),
                        new MaskSelector([false, true]),
                    ])
                ),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new IndexListSelector([0, 2, 4, 6, 8]),
                        new IndexListSelector([0, 2, 4]),
                        new IndexListSelector([0, 2]),
                        new IndexListSelector([1]),
                    ])
                ),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector('::2'),
                        new SliceSelector('::2'),
                        new SliceSelector('::2'),
                        new SliceSelector('1:'),
                    ])
                ),
                [99],
                [1, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview(
                    new PipeSelector([
                        new SliceSelector(new Slice(null, null, 2)),
                        new SliceSelector('::2'),
                        new SliceSelector('::2'),
                    ])
                ),
                [11, 99],
                [11, 2, 3, 4, 5, 6, 7, 8, 99, 10],
            ],
        ];
    }

    public function dataProviderForApply(): array
    {
        return [
            [
                [],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $item) => $item + 1,
                [],
            ],
            [
                [1],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $item) => $item + 1,
                [2],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $item) => $item,
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $item) => $item + 1,
                [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $item, int $index) => $item + $index,
                [1, 3, 5, 7, 9, 11, 13, 15, 17, 19],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2'),
                fn (int $item, int $index) => $item + $index,
                [1, 2, 4, 4, 7, 6, 10, 8, 13, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('1::2'),
                fn (int $item) => $item * 2,
                [1, 4, 3, 8, 5, 12, 7, 16, 9, 20],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('1::2')
                    ->subview(new IndexListSelector([0, 1, 2])),
                fn (int $item) => $item * 2,
                [1, 4, 3, 8, 5, 12, 7, 8, 9, 10],
            ],
        ];
    }

    public function dataProviderForApplyWith(): array
    {
        return [
            [
                [],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $lhs, int $rhs) => $lhs + $rhs,
                [],
                [],
            ],
            [
                [1],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $lhs, int $rhs) => $lhs + $rhs,
                [2],
                [3],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $lhs, int $rhs) => $lhs + $rhs,
                [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                [11, 22, 33, 44, 55, 66, 77, 88, 99, 110],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source),
                fn (int $lhs, int $rhs, int $index) => $index % 2 === 0 ? $lhs : $rhs,
                [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                [1, 20, 3, 40, 5, 60, 7, 80, 9, 100],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview('::2'),
                fn (int $lhs, int $rhs) => $lhs * $rhs,
                [1, 2, 3, 4, 5],
                [1, 2, 6, 4, 15, 6, 28, 8, 45, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)->subview('::2'),
                fn (int $lhs, int $rhs) => $lhs * $rhs,
                10,
                [10, 2, 30, 4, 50, 6, 70, 8, 90, 10],
            ],
        ];
    }
}
