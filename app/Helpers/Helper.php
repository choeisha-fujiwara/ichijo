<?php
use Carbon\Carbon;

if (! function_exists('hyphenConvert')) {
    function hyphenConvert($value) {
        $hyphens = '/[\x{207B}\x{208B}\x{2010}\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}\x{2500}\x{2501}\x{2796}\x{30FC}\x{3161}\x{FF0D}\x{FF70}]/u';
        $value = mb_convert_kana($value, "KVa");
        $res = preg_replace($hyphens, '-', $value);
        $res = str_replace('-', '', $res);
        return $res;
    }
}