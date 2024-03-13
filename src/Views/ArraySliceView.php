<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Structs\NormalizedSlice;
use Smoren\ArrayView\Structs\Slice;

/**
 * Class representing a slice-based view of an array or another ArrayView
 * for accessing elements within a specified slice range.
 *
 * <pre>
 *
 * $source = [1, 2, 3, 4, 5];
 * $view = ArrayView::toView($source)->subview(new SliceSelector('::2'));
 * $view->toArray(); // [1, 3, 5]
 *
 * </pre>
 *
 * @template T
 *
 * @extends ArrayView<T>
 */
class ArraySliceView extends ArrayView
{
    /**
     * @var NormalizedSlice The normalized slice range defining the view within the source array or ArrayView.
     */
    protected NormalizedSlice $slice;

    /**
     * Constructs a new ArraySliceView instance with the specified source array or ArrayView and slice range.
     *
     * @param Array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param Slice $slice The slice range specifying the subset of elements to include in the view.
     * @param bool|null $readonly Optional flag to indicate whether the view should be readonly.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
     */
    public function __construct(&$source, Slice $slice, ?bool $readonly = null)
    {
        parent::__construct($source, $readonly);
        $this->slice = $slice->normalize(\count($source));
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->slice);
    }

    /**
     * {@inheritDoc}
     */
    protected function convertIndex(int $i): int
    {
        return $this->slice->convertIndex($i);
    }
}
