<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Date
{

    /**
     * Undocumented function
     *
     * @param [type] $from
     * @param [type] $to
     * @param [type] $type
     * @return void
     */
    public static function difference($from, $to, $type = null)
    {
        $d1 = new \DateTime($from);
        $d2 = new \DateTime($to);
        $diff = $d2->diff($d1);
        if ($type == null) {
            //return array
            return $diff;
        } else {
            return $diff->$type;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $startDate
     * @param [type] $endDate
     * @param boolean $weekendDays
     * @return void
     */
    public static function businessDays($startDate, $endDate, $weekendDays = false)
    {
        $begin = strtotime($startDate);
        $end = strtotime($endDate);

        if ($begin > $end) {
            //startDate is in the future
            return 0;
        } else {
            $numDays = 0;
            $weekends = 0;

            while ($begin <= $end) {
                $numDays++; // no of days in the given interval
                $whatDay = date('N', $begin);

                if ($whatDay > 5) { // 6 and 7 are weekend days
                    $weekends++;
                }
                $begin+=86400; // +1 day
            };

            if ($weekendDays == true) {
                return $weekends;
            }

            $working_days = $numDays - $weekends;
            return $working_days;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $startDate
     * @param [type] $endDate
     * @param integer $nonWork
     * @return void
     */
    public static function businessDates($startDate, $endDate, $nonWork = 6)
    {
        $begin    = new \DateTime($startDate);
        $end      = new \DateTime($endDate);
        $holiday  = array();
        $interval = new \DateInterval('P1D');
        $dateRange= new \DatePeriod($begin, $interval, $end);
        $dates = array();
        foreach ($dateRange as $date) {
            if ($date->format("N") < $nonWork and !in_array($date->format("Y-m-d"), $holiday)) {
                $dates[] = $date->format("Y-m-d");
            }
        }
        return $dates;
    }

    /**
     * Undocumented function
     *
     * @param integer $month
     * @param string $year
     * @return void
     */
    public static function daysInMonth($month = 0, $year = '')
    {
        if ($month < 1 or $month > 12) {
            return 0;
        } elseif (!is_numeric($year) or strlen($year) !== 4) {
            $year = date('Y');
        }
        if (defined('CAL_GREGORIAN')) {
            return cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }
        if ($year >= 1970) {
            return (int) date('t', mktime(12, 0, 0, $month, 1, $year));
        }
        if ($month == 2) {
            if ($year % 400 === 0 or ( $year % 4 === 0 && $year % 100 !== 0)) {
                return 29;
            }
        }
        $days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        return $days_in_month[$month - 1];
    }

    /**
     * Undocumented function
     *
     * @param [type] $birthDate
     * @return void
     */
    public static function ageFromBirthDate($birthDate)
    {
        $date = new \DateTime($birthDate);
        $now = new \DateTime();

        $interval = $now->diff($date);
        return $interval->y;
    }
    
}
