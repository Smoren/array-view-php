<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Tests\Unit\Examples;

use Smoren\ArrayView\Views\ArrayView;

class BenchTest extends \Codeception\Test\Unit
{
    private $n = 10000;

    public function testReadSliceView()
    {
        $n = $this->n;
        $originalArray = range(0, $n);

        $ts = \microtime(true);
        $view = ArrayView::toView($originalArray);
        $result = $view['::2'];
        $spent = \round(\microtime(true) - $ts, 4);

        echo "[testReadSliceView] SPENT: {$spent} s\n";
        ob_flush();
    }

    public function testReadSlicePure()
    {
        $n = $this->n;
        $n_2 = intval($n / 2);
        $originalArray = range(0, $n);

        $ts = \microtime(true);
        $result = [];
        for ($i = 0; $i < $n_2; $i++) {
            $result[$i] = $originalArray[$i * 2];
        }
        $spent = \round(\microtime(true) - $ts, 4);

        echo "[testReadSlicePure] SPENT: {$spent} s\n";
        ob_flush();
    }
    public function testWriteSliceView()
    {
        $n = $this->n;
        $n_2 = intval($n / 2);
        $originalArray = range(0, $n);
        $toWrite = range(0, $n_2);

        $ts = \microtime(true);
        $view = ArrayView::toView($originalArray);
        $view['::2'] = $toWrite;
        $spent = \round(\microtime(true) - $ts, 4);

        echo "[testWriteSliceView] SPENT: {$spent} s\n";
        ob_flush();
    }

    public function testWriteSlicePure()
    {
        $n = $this->n;
        $n_2 = intval($n / 2);
        $originalArray = range(0, $n);
        $toWrite = range(0, $n_2);

        $ts = \microtime(true);
        for ($i = 0; $i < $n_2; $i++) {
            $originalArray[$i * 2] = $toWrite[$i];
        }
        $spent = \round(\microtime(true) - $ts, 4);

        echo "[testWriteSlicePure] SPENT: {$spent} s\n";
        ob_flush();
    }
}
