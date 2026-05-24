<?php
namespace App\Helpers;

class Helper
{
    function formatNumber($number) {
        // Check if it's a float but has no decimal part
        if (intval($number) == $number) {
            return intval($number); // return as int
        }
        return $number; // return as is (with decimal)
    }
    

}
