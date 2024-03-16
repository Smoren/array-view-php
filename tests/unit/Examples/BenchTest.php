<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Examples;

use Smoren\ArrayView\Views\ArrayView;

class BenchTest extends \Codeception\Test\Unit
{
    public function testWriteSliceView()
    {
        $n = 10000;
        $n_2 = intval($n / 2);
        $originalArray = range(0, $n);
        $toWrite = range(0, $n_2);

        $ts = \microtime(true);
        $view = ArrayView::toView($originalArray);
        $view['::2'] = $toWrite;
        $spent = \round(\microtime(true) - $ts, 4);

        echo "SPENT: {$spent} s\n";
        ob_flush();
    }

    public function testWriteSlicePure()
    {
        $n = 10000;
        $n_2 = intval($n / 2);
        $originalArray = range(0, $n);
        $toWrite = range(0, $n_2);

        $ts = \microtime(true);
        for ($i = 0; $i < $n_2; $i++) {
            $originalArray[$i * 2] = $toWrite[$i];
        }
        $spent = \round(\microtime(true) - $ts, 4);

        echo "SPENT: {$spent} s\n";
        ob_flush();
    }
}
