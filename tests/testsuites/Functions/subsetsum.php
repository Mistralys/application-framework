<?php

use PHPUnit\Framework\TestCase;

final class Functions_SubsetSumTest extends TestCase
{
    public function test_subsetsum() : void
    {
        $result = subset_sum(array(5,10,7,3,20), 25);
        $expected = array(
            array(3, 5, 7, 10),
            array(5, 20)
        );
        
        $this->assertEquals($expected, $result);
    }
}
