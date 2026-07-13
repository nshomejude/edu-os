<?php

namespace App\Support;

/** Code 39 barcode rendered as inline SVG — used on printable custody documents. */
class Barcode
{
    private const MAP = [
        '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw', 'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn', 'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn', 'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww', 'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn', 'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw', 'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw', '.' => 'wwnnnnwnn', ' ' => 'nwwnnnwnn', '*' => 'nwnnwnwnn',
        '$' => 'nwnwnwnnn', '/' => 'nwnwnnnwn', '+' => 'nwnnnwnwn', '%' => 'nnnwnwnwn',
    ];

    public static function svg(string $text, int $height = 46): string
    {
        $clean = preg_replace('/[^0-9A-Z\-\. \$\/\+%]/', '-', strtoupper($text));
        $chars = str_split('*'.$clean.'*');
        $narrow = 2;
        $wide = 6;
        $x = 0;
        $bars = '';
        foreach ($chars as $ch) {
            $pattern = self::MAP[$ch] ?? self::MAP['-'];
            foreach (str_split($pattern) as $i => $p) {
                $w = $p === 'w' ? $wide : $narrow;
                if ($i % 2 === 0) {   // even positions are bars, odd are spaces
                    $bars .= sprintf('<rect x="%d" y="0" width="%d" height="%d" fill="#1C1D1F"/>', $x, $w, $height);
                }
                $x += $w;
            }
            $x += $narrow;   // inter-character gap
        }

        return sprintf(
            '<svg viewBox="0 0 %d %d" height="%d" style="max-width:100%%" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">%s</svg>',
            $x, $height, $height, $bars
        );
    }
}
