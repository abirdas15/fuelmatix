<?php

namespace App\Helpers;

use Carbon\Carbon;

class Helpers
{
    /**
     * Formats a given date string into a specified format.
     *
     * This method takes a date string, converts it into a standardized
     * 'Y-m-d H:i:s' format, then creates a Carbon instance from this date
     * with the specified timezone from SessionUser::TIMEZONE. It then converts
     * the timezone to 'Asia/Dhaka' and returns the date in the desired format.
     *
     * @param string $date The date string to be formatted. Expected in a parseable format.
     * @param string $format The desired output format for the date string. Default is 'd/m/Y'.
     * @return string|null The formatted date string, or null if the input date is empty.
     */
    public static function formatDate(string $date, string $format = 'd/m/Y'): ?string
    {
        if (empty($date)) {
            return null;
        }
        $date = date('Y-m-d H:i:s', strtotime($date));
        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $date, SessionUser::TIMEZONE)
            ->setTimezone('Asia/Dhaka');
        return $timestamp->format($format);
    }



    public static function convertNumberToWord($num = false)
    {
        $num    = (string) ((int) $num);
        if ((int) ($num) && ctype_digit($num)) {
            $words  = array();
            $num    = str_replace(array(',', ' '), '', trim($num));

            $list1  = array(
                '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
                'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen',
                'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
            );

            $list2  = array(
                '', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty',
                'seventy', 'eighty', 'ninety', 'hundred'
            );

            $list3  = array(
                '', 'thousand', 'million', 'billion', 'trillion',
                'quadrillion', 'quintillion', 'sextillion', 'septillion',
                'octillion', 'nonillion', 'decillion', 'undecillion',
                'duodecillion', 'tredecillion', 'quattuordecillion',
                'quindecillion', 'sexdecillion', 'septendecillion',
                'octodecillion', 'novemdecillion', 'vigintillion'
            );

            $num_length = strlen($num);
            $levels = (int) (($num_length + 2) / 3);
            $max_length = $levels * 3;
            $num    = substr('00' . $num, -$max_length);
            $num_levels = str_split($num, 3);

            foreach ($num_levels as $num_part) {
                $levels--;
                $hundreds   = (int) ($num_part / 100);
                $hundreds   = ($hundreds ? ' ' . $list1[$hundreds] . ' Hundred' . ($hundreds == 1 ? '' : 's') . ' ' : '');
                $tens       = (int) ($num_part % 100);
                $singles    = '';

                if ($tens < 20) {
                    $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
                } else {
                    $tens = (int) ($tens / 10);
                    $tens = ' ' . $list2[$tens] . ' ';
                    $singles = (int) ($num_part % 10);
                    $singles = ' ' . $list1[$singles] . ' ';
                }
                $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_part)) ? ' ' . $list3[$levels] . ' ' : '');
            }
            $commas = count($words);
            if ($commas > 1) {
                $commas = $commas - 1;
            }

            $words  = implode(', ', $words);

            $words  = trim(str_replace(' ,', ',', ucwords($words)), ', ');
            if ($commas) {
                $words  = str_replace(',', ' and', $words);
            }

            return $words;
        } else if (!((int) $num)) {
            return 'Zero';
        }
        return '';
    }

    /**
     * @param array $data
     * @param int $value
     * @param string $matchField
     * @param string $findField
     * @return int|mixed
     */
    public static function filterBstiChart(array $data, int $value, string $matchField, string $findField)
    {
        if ($value == 0) {
            return 0;
        }
        $dataArray = [];
        foreach ($data as $row) {
            $dataArray[$row[$findField]] = $row;
        }
        return $dataArray[$value][$matchField] ?? 0;
    }
}
