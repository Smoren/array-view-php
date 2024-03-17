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
}
