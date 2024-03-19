<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Examples;

use Smoren\ArrayView\Selectors\IndexListSelector;
use Smoren\ArrayView\Selectors\MaskSelector;
use Smoren\ArrayView\Selectors\PipeSelector;
use Smoren\ArrayView\Selectors\SliceSelector;
use Smoren\ArrayView\Views\ArrayView;

class ExamplesTest extends \Codeception\Test\Unit
{
    public function testSlicing()
    {
        $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $originalView = ArrayView::toView($originalArray);

        $this->assertSame([2, 4, 6], $originalView['1:7:2']);
        $this->assertSame([1, 2, 3], $originalView[':3']);
        $this->assertSame([9, 8, 7, 6, 5, 4, 3, 2, 1], $originalView['::-1']);

        $this->assertSame(3, $originalView[2]);
        $this->assertSame(5, $originalView[4]);
        $this->assertSame(9, $originalView[-1]);
        $this->assertSame(8, $originalView[-2]);

        $originalView['1:7:2'] = [22, 44, 66];
        $this->assertSame([1, 22, 3, 44, 5, 66, 7, 8, 9], $originalArray);
    }

    public function testSubview()
    {
        $originalArray = [1, 2, 3, 4, 5];
        $originalView = ArrayView::toView($originalArray);

        $this->assertSame(
            [1, 3, 5],
            $originalView->subview(new MaskSelector([true, false, true, false, true]))->toArray(),
        );
        $this->assertSame(
            [2, 3, 5],
            $originalView->subview(new IndexListSelector([1, 2, 4]))->toArray(),
        );
        $this->assertSame(
            [5, 4, 3, 2, 1],
            $originalView->subview(new SliceSelector('::-1'))->toArray(),
        );

        $this->assertSame(
            [1, 3, 5],
            $originalView->subview([true, false, true, false, true])->toArray(),
        );
        $this->assertSame(
            [2, 3, 5],
            $originalView->subview([1, 2, 4])->toArray(),
        );
        $this->assertSame(
            [5, 4, 3, 2, 1],
            $originalView->subview('::-1')->toArray(),
        );

        $originalView->subview(new MaskSelector([true, false, true, false, true]))
            ->apply(fn(int $x) => $x * 10);

        $this->assertSame([10, 2, 30, 4, 50], $originalArray);
    }

    public function testSubarray()
    {
        $originalArray = [1, 2, 3, 4, 5];
        $originalView = ArrayView::toView($originalArray);

        $this->assertSame(
            [1, 3, 5],
            $originalView[new MaskSelector([true, false, true, false, true])],
        );
        $this->assertSame(
            [2, 3, 5],
            $originalView[new IndexListSelector([1, 2, 4])],
        );
        $this->assertSame(
            [5, 4, 3, 2, 1],
            $originalView[new SliceSelector('::-1')],
        );

        $this->assertSame(
            [1, 3, 5],
            $originalView[[true, false, true, false, true]],
        );
        $this->assertSame(
            [2, 3, 5],
            $originalView[[1, 2, 4]],
        );
        $this->assertSame(
            [5, 4, 3, 2, 1],
            $originalView['::-1'],
        );

        $originalView[new MaskSelector([true, false, true, false, true])] = [10, 30, 50];

        $this->assertSame([10, 2, 30, 4, 50], $originalArray);
    }

    public function testCombinedSubview()
    {
        $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $subview = ArrayView::toView($originalArray)
            ->subview(new SliceSelector('::2')) // [1, 3, 5, 7, 9]
            ->subview(new MaskSelector([true, false, true, true, true])) // [1, 5, 7, 9]
            ->subview(new IndexListSelector([0, 1, 2])) // [1, 5, 7]
            ->subview(new SliceSelector('1:')); // [5, 7]

        $this->assertSame([5, 7], $subview->toArray());
        $this->assertSame([5, 7], $subview[':']);

        $subview[':'] = [55, 77];
        $this->assertSame([1, 2, 3, 4, 55, 6, 77, 8, 9, 10], $originalArray);
    }

    public function testCombinedSubviewShort()
    {
        $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $subview = ArrayView::toView($originalArray)
            ->subview('::2') // [1, 3, 5, 7, 9]
            ->subview([true, false, true, true, true]) // [1, 5, 7, 9]
            ->subview([0, 1, 2]) // [1, 5, 7]
            ->subview('1:'); // [5, 7]

        $this->assertSame([5, 7], $subview->toArray());
        $this->assertSame([5, 7], $subview[':']);

        $subview[':'] = [55, 77];
        $this->assertSame([1, 2, 3, 4, 55, 6, 77, 8, 9, 10], $originalArray);
    }

    public function testSelectorsPipe()
    {
        $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $selector = new PipeSelector([
            new SliceSelector('::2'),
            new MaskSelector([true, false, true, true, true]),
            new IndexListSelector([0, 1, 2]),
            new SliceSelector('1:'),
        ]);

        $view = ArrayView::toView($originalArray);
        $this->assertTrue(isset($view[$selector]));

        $subview = $view->subview($selector);

        $this->assertSame([5, 7], $subview->toArray());
        $this->assertSame([5, 7], $subview[':']);

        $subview[':'] = [55, 77];
        $this->assertSame([1, 2, 3, 4, 55, 6, 77, 8, 9, 10], $originalArray);
    }

    public function testSelectorsPipeNested()
    {
        $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $selector = new PipeSelector([
            new SliceSelector('::2'),
            new PipeSelector([
                new MaskSelector([true, false, true, true, true]),
                new IndexListSelector([0, 1, 2]),
            ]),
            new SliceSelector('1:'),
        ]);

        $view = ArrayView::toView($originalArray);
        $this->assertTrue(isset($view[$selector]));

        $subview = $view->subview($selector);

        $this->assertSame([5, 7], $subview->toArray());
        $this->assertSame([5, 7], $subview[':']);

        $subview[':'] = [55, 77];
        $this->assertSame([1, 2, 3, 4, 55, 6, 77, 8, 9, 10], $originalArray);
    }

    public function testMap()
    {
        $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $subview = ArrayView::toView($source)->subview('::2');

        $actual = $subview->map(fn ($x) => $x * 10);
        $this->assertSame([10, 30, 50, 70, 90], $actual);
    }

    public function testMapWith()
    {
        $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $subview = ArrayView::toView($source)->subview('::2');
        $this->assertSame([1, 3, 5, 7, 9], $subview->toArray());

        $data = [9, 27, 45, 63, 81];

        $actual = $subview->mapWith($data, fn ($lhs, $rhs) => $lhs + $rhs);
        $this->assertSame([10, 30, 50, 70, 90], $actual);
    }

    public function testIs()
    {
        $source = [1, 2, 3, 4, 5, 6];
        $view = ArrayView::toView($source);

        $mask = $view->is(fn ($x) => $x % 2 === 0);
        $this->assertSame([false, true, false, true, false, true], $mask->getValue());

        $this->assertSame([2, 4, 6], $view->subview($mask)->toArray());
        $this->assertSame([2, 4, 6], $view[$mask]);

        $view[$mask] = [20, 40, 60];
        $this->assertSame([1, 20, 3, 40, 5, 60], $source);
    }

    public function testMatch()
    {
        $source = [1, 2, 3, 4, 5, 6];
        $view = ArrayView::toView($source);

        $mask = $view->match(fn ($x) => $x % 2 === 0);
        $this->assertSame([false, true, false, true, false, true], $mask->getValue());

        $this->assertSame([2, 4, 6], $view->subview($mask)->toArray());
        $this->assertSame([2, 4, 6], $view[$mask]);

        $view[$mask] = [20, 40, 60];
        $this->assertSame([1, 20, 3, 40, 5, 60], $source);
    }

    public function testMatchWith()
    {
        $source = [1, 2, 3, 4, 5, 6];
        $view = ArrayView::toView($source);

        $data = [6, 5, 4, 3, 2, 1];

        $mask = $view->matchWith($data, fn ($lhs, $rhs) => $lhs > $rhs);
        $this->assertSame([false, false, false, true, true, true], $mask->getValue());

        $this->assertSame([4, 5, 6], $view->subview($mask)->toArray());
        $this->assertSame([4, 5, 6], $view[$mask]);

        $view[$mask] = [40, 50, 60];
        $this->assertSame([1, 2, 3, 40, 50, 60], $source);
    }

    public function testApply()
    {
        $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $subview = ArrayView::toView($source)->subview('::2');

        $this->assertSame([1, 3, 5, 7, 9], $subview->toArray());

        $subview->apply(fn ($x) => $x * 10);

        $this->assertSame([10, 30, 50, 70, 90], $subview->toArray());
        $this->assertSame([10, 2, 30, 4, 50, 6, 70, 8, 90, 10], $source);
    }

    public function testApplyWith()
    {
        $source = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $subview = ArrayView::toView($source)->subview('::2');

        $this->assertSame([1, 3, 5, 7, 9], $subview->toArray());

        $data = [9, 27, 45, 63, 81];

        $subview->applyWith($data, fn ($lhs, $rhs) => $lhs + $rhs);
        $this->assertSame([10, 30, 50, 70, 90], $subview->toArray());

        $this->assertSame([10, 2, 30, 4, 50, 6, 70, 8, 90, 10], $source);
    }

    public function testFilter()
    {
        $source = [1, 2, 3, 4, 5, 6];
        $view = ArrayView::toView($source);

        $filtered = $view->filter(fn ($x) => $x % 2 === 0);
        $this->assertSame([2, 4, 6], $filtered->toArray());

        $filtered[':'] = [20, 40, 60];
        $this->assertSame([20, 40, 60], $filtered->toArray());

        $this->assertSame([1, 20, 3, 40, 5, 60], $source);
    }

    public function testCount()
    {
        $source = [1, 2, 3, 4, 5];

        $subview = ArrayView::toView($source)->subview('::2');

        $this->assertSame([1, 3, 5], $subview->toArray());
        $this->assertCount(3, $subview);
    }

    public function testIterator()
    {
        $source = [1, 2, 3, 4, 5];
        $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5]

        $actual = [];
        foreach ($subview as $item) {
            $actual[] = $item;
            // 1, 3, 5
        }
        $this->assertSame([1, 3, 5], $actual);
        $this->assertSame([1, 3, 5], [...$subview]);
    }

    public function testIsReadonly()
    {
        $source = [1, 2, 3, 4, 5];

        $readonlyView = ArrayView::toView($source, true);
        $this->assertTrue($readonlyView->isReadonly());

        $readonlySubview = ArrayView::toView($source)->subview('::2', true);
        $this->assertTrue($readonlySubview->isReadonly());

        $view = ArrayView::toView($source);
        $this->assertFalse($view->isReadonly());

        $subview = ArrayView::toView($source)->subview('::2');
        $this->assertFalse($subview->isReadonly());
    }

    public function testOffsetExists()
    {
        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $this->assertTrue(isset($view[0]));
        $this->assertTrue(isset($view[-1]));
        $this->assertFalse(isset($view[10]));

        $this->assertTrue(isset($view[new SliceSelector('::2')]));
        $this->assertTrue(isset($view[new IndexListSelector([0, 2, 4])]));
        $this->assertFalse(isset($view[new IndexListSelector([0, 2, 10])]));
        $this->assertTrue(isset($view[new MaskSelector([true, true, false, false, true])]));
        $this->assertFalse(isset($view[new MaskSelector([true, true, false, false, true, true])]));

        $this->assertTrue(isset($view['::2']));
        $this->assertTrue(isset($view[[0, 2, 4]]));
        $this->assertFalse(isset($view[[0, 2, 10]]));
        $this->assertTrue(isset($view[[true, true, false, false, true]]));
        $this->assertFalse(isset($view[[true, true, false, false, true, true]]));
    }

    public function testOffsetGet()
    {
        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $this->assertSame(1, $view[0]);
        $this->assertSame(5, $view[-1]);

        $this->assertSame([1, 3, 5], $view[new SliceSelector('::2')]);
        $this->assertSame([1, 3, 5], $view[new IndexListSelector([0, 2, 4])]);
        $this->assertSame([1, 2, 5], $view[new MaskSelector([true, true, false, false, true])]);

        $this->assertSame([1, 3, 5], $view['::2']);
        $this->assertSame([1, 3, 5], $view[[0, 2, 4]]);
        $this->assertSame([1, 2, 5], $view[[true, true, false, false, true]]);
    }

    public function testOffsetSet()
    {
        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $view[0] = 11;
        $view[-1] = 55;

        $this->assertSame([11, 2, 3, 4, 55], $source);

        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $view[new SliceSelector('::2')] = [11, 33, 55];
        $this->assertSame([11, 2, 33, 4, 55], $source);

        $view[new IndexListSelector([1, 3])] = [22, 44];
        $this->assertSame([11, 22, 33, 44, 55], $source);

        $view[new MaskSelector([true, false, false, false, true])] = [111, 555];
        $this->assertSame([111, 22, 33, 44, 555], $source);

        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $view['::2'] = [11, 33, 55];
        $this->assertSame([11, 2, 33, 4, 55], $source);

        $view[[1, 3]] = [22, 44];
        $this->assertSame([11, 22, 33, 44, 55], $source);

        $view[[true, false, false, false, true]] = [111, 555];
        $this->assertSame([111, 22, 33, 44, 555], $source);
    }

    public function testSet()
    {
        $source = [1, 2, 3, 4, 5];
        $subview = ArrayView::toView($source)->subview('::2'); // [1, 3, 5]

        $subview->set([11, 33, 55]);
        $this->assertSame([11, 33, 55], $subview->toArray());
        $this->assertSame([11, 2, 33, 4, 55], $source);
    }

    public function testToUnlinkedView()
    {
        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toUnlinkedView($source);

        $this->assertSame(1, $view[0]);
        $this->assertSame([2, 4], $view['1::2']);
        $view['1::2'] = [22, 44];

        $this->assertSame([1, 22, 3, 44, 5], $view->toArray());
        $this->assertSame([1, 2, 3, 4, 5], $source);
    }

    public function testToView()
    {
        $source = [1, 2, 3, 4, 5];
        $view = ArrayView::toView($source);

        $this->assertSame(1, $view[0]);
        $this->assertSame([2, 4], $view['1::2']);
        $view['1::2'] = [22, 44];

        $this->assertSame([1, 22, 3, 44, 5], $view->toArray());
        $this->assertSame([1, 22, 3, 44, 5], $source);
    }
}
