<?php

namespace Smoren\ArrayView\Tests\Unit\ArrayView;

use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Views\ArrayView;

class ReadonlyTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForReadonly
     * @dataProvider dataProviderForReadonlySubview
     */
    public function testWriteByIndex(array $source, callable $readonlyViewGetter, array $expected)
    {
        $view = $readonlyViewGetter($source);

        for ($i = 0; $i < \count($view); ++$i) {
            try {
                $view[$i] = 1;
                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(ReadonlyError::class, $e);
                $this->assertSame("Cannot modify a readonly view.", $e->getMessage());
            }
        }

        $this->assertSame($expected, [...$view]);
    }

    /**
     * @dataProvider dataProviderForReadonly
     * @dataProvider dataProviderForReadonlySubview
     */
    public function testAll(array $source, callable $readonlyViewGetter, array $expected)
    {
        $view = $readonlyViewGetter($source);

        try {
            $view[':'] = [...$view];
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ReadonlyError::class, $e);
            $this->assertSame("Cannot modify a readonly view.", $e->getMessage());
            $this->assertSame($expected, [...$view]);
        }

        $this->assertSame($expected, [...$view]);
    }

    /**
     * @dataProvider dataProviderForReadonly
     * @dataProvider dataProviderForReadonlySubview
     */
    public function testApply(array $source, callable $readonlyViewGetter, array $expected)
    {
        $view = $readonlyViewGetter($source);

        try {
            $view->apply(fn ($item) => $item);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ReadonlyError::class, $e);
            $this->assertSame("Cannot modify a readonly view.", $e->getMessage());
            $this->assertSame($expected, [...$view]);
        }

        $this->assertSame($expected, [...$view]);
    }

    /**
     * @dataProvider dataProviderForReadonly
     * @dataProvider dataProviderForReadonlySubview
     */
    public function testApplyWith(array $source, callable $readonlyViewGetter, array $expected)
    {
        $view = $readonlyViewGetter($source);

        try {
            $view->applyWith([...$view], fn ($lhs, $rhs) => $lhs + $rhs);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ReadonlyError::class, $e);
            $this->assertSame("Cannot modify a readonly view.", $e->getMessage());
            $this->assertSame($expected, [...$view]);
        }

        $this->assertSame($expected, [...$view]);
    }

    /**
     * @dataProvider dataProviderForReadonlyCreateError
     */
    public function testCreateError(array $source, callable $readonlyViewGetter)
    {
        $this->expectException(ReadonlyError::class);
        $this->expectExceptionMessage("Cannot create non-readonly view for readonly source.");
        $readonlyViewGetter($source);
    }

    public function dataProviderForReadonly(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true),
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toUnlinkedView(ArrayView::toView($source, true), true),
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2'),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', true)
                    ->subview(new MaskSelector([true, false, true, false, true])),
                [1, 5, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview('1:'),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true]))
                    ->subview(new MaskSelector([false, true])),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new IndexListSelector([0, 2, 4, 6, 8]))
                    ->subview(new IndexListSelector([0, 2, 4]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview(new IndexListSelector([1])),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2'),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('1:'),
                [9],
            ],
        ];
    }

    public function dataProviderForReadonlySubview(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', true),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toUnlinkedView(new ArrayView($source))
                    ->subview('::2', true),
                [1, 3, 5, 7, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', true)
                    ->subview(new MaskSelector([true, false, true, false, true])),
                [1, 5, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]), true)
                    ->subview(new IndexListSelector([0, 2])),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', true)
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]), true)
                    ->subview('1:'),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]), true)
                    ->subview(new MaskSelector([true, false, true, false, true]), true)
                    ->subview(new MaskSelector([true, false, true]), true)
                    ->subview(new MaskSelector([false, true]), true),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new MaskSelector([true, false, true]), true),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview(new IndexListSelector([0, 2, 4, 6, 8]))
                    ->subview(new IndexListSelector([0, 2, 4]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview(new IndexListSelector([1]), true),
                [9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview('::2', true)
                    ->subview('::2'),
                [1, 9],
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('::2')
                    ->subview('1:', true),
                [9],
            ],
        ];
    }

    public function dataProviderForReadonlyCreateError(): array
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', false),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toUnlinkedView(ArrayView::toView($source), true)
                    ->subview('::2', false),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2')
                    ->subview(new MaskSelector([true, false, true, false, true]), false),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source)
                    ->subview('::2', true)
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]), false),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', false)
                    ->subview(new MaskSelector([true, false, true, false, true]))
                    ->subview(new IndexListSelector([0, 2]))
                    ->subview('1:'),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, false)
                    ->subview(new MaskSelector([true, false, true, false, true, false, true, false, true, false]))
                    ->subview(new MaskSelector([true, false, true, false, true]), true)
                    ->subview(new MaskSelector([true, false, true]), false)
                    ->subview(new MaskSelector([false, true])),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, true)
                    ->subview('::2', false)
                    ->subview('::2')
                    ->subview('::2'),
            ],
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                fn (array &$source) => ArrayView::toView($source, false)
                    ->subview('::2', true)
                    ->subview('::2', false)
                    ->subview('::2', true)
                    ->subview('1:', false),
            ],
        ];
    }
}
