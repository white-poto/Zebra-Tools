<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 14-12-3
 * Time: 下午12:18
 */

namespace Jenner\Zebra\Tools;


class CommonDate {
    /**
     * 获取一整年的星期列表，key为一年中的第几个星期，value为该星期的开始和结束日期
     * @param $year
     * @return array
     */
    public static function getWeeksOfYear($year) {
        $year_start = $year . "-01-01";
        $year_end = $year . "-12-31";

        $week_count = date('W', strtotime('last week', $year_end));
        if(date('w', strtotime($year_start)) == 1){
            $week_start = $year_start;
        }else{
            $week_start = date('Y-m-d', strtotime("next monday", strtotime($year_start)));
        }

        $weeks = [];
        for($i=1; $i<=$week_count; $i++){
            $week_end = date('Y-m-d', strtotime($week_start . '+6 Day'));
            $weeks[$i] = [$week_start, $week_end];
            $week_start = date('Y-m-d', strtotime($week_start . '+7 Day'));
        }

        return $weeks;
    }

    /**
     * 根据日期，获取该日期所在的周信息
     * @param $date
     * @return bool
     */
    public static function getWeekStartEndByDate($date){
        $weeks = self::getWeeksOfYear(substr($date, 0, 4));
        if(empty($weeks)) return false;
        foreach($weeks as $week){
            if($date>=$week[0] && $date<=$week[1]){
                return $week;
            }
        }
        return false;
    }

    /**
     * 根据日期获取当月的所有周信息，支持传入Y-m，Y-m-d格式参数
     * @param $date
     * @return array|bool
     */
    public static function getWeeksInMonth($date){
        $year = substr($date, 0, 4);
        $weeks = self::getWeeksOfYear($year);
        $start_date = date('Y-m-01', strtotime($date));
        $end_date = date('Y-m-31', strtotime($date));

        $week_result = [];
        foreach($weeks as $key=>$week){
            if($week[0]>=$start_date && $week[0]<=$end_date){
                $week_result[] = ['year'=>$year, 'week_num'=>$key];
            }
        }
        if(empty($week_result)) return false;

        return $week_result;
    }
} 