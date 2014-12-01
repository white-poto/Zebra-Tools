<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 14-11-7
 * Time: 上午9:41
 */

namespace Jenner\Zebra\Tools;


    /**
     * Class CommonArray
     * @package Jenner\Zebra\Tools
     */
/**
 * Class CommonArray
 * @package Jenner\Zebra\Tools
 */
class CommonArray {
    /**
     * 根据列的Key获取一个二维数组中某一列的值，作为一维数组返回
     * @param $column
     * @param $array
     * @return array
     */
    public static function columnValue($column, $array){
        $result = [];
        $array = (array)$array;
        foreach($array as $value){
            $value = (array)$value;
            $result[] = empty($value[$column]) ? null : $value[$column];
        }
        return $result;
    }

    /**
     * 根据field获取对象的属性获作为一个数组返回
     * @param $filed
     * @param $object
     * @return array
     */
    public static function fieldValue($filed, $object){
        $result = [];
        $object = (object)$object;
        foreach($object as $value){
            $result[] = isset($value->$filed) ? false : $value->$filed;
        }
        return $result;
    }

    /**
     * 类似SQL ORDER BY 的多为数组排序函数
     * example: $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     *
     * @return mixed
     */
    public static function arrayOrderby()
    {
        $args = \func_get_args();
        $data = \array_shift($args);
        foreach ($args as $n => $field) {
            if (\is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = & $data;
        \call_user_func_array('array_multisort', $args);
        return \array_pop($args);
    }

    /**
     * 对象转换成数组
     * @param $obj
     * @return mixed
     */
    public static function objectToArray($obj){
        $_arr = is_object($obj)? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? self::objectToArray($val) : $val;
            $arr[$key] = $val;
        }

        return $arr;
    }


    /**
     * 将一个二维数组，以其中一列为KEY，一列为VALUE，返回一个二维数组
     * @param $array
     * @param $array_key_key
     * @param $array_value_key
     * @return array
     */
    public static function tableToArrayMapping(array $array, $array_key_key, $array_value_key=null){
        $result = [];
        foreach($array as $arr){
            if(!is_array($arr)) continue;
            $key = $arr[$array_key_key];
            if(is_null($array_value_key)){
                $value = $arr;
            }else{
                $value = $arr[$array_value_key];
            }
            //区别在这里
            $result[$key][] = $value;
        }

        return $result;
    }

    /**
     * 将一个二维数组，以其中一列为KEY，一列为VALUE，返回一个一维数组
     * @param $array
     * @param $array_key_key
     * @param $array_value_key
     * @return array
     */
    public static function tableToMapping(array $array, $array_key_key, $array_value_key=null){
        $result = [];
        foreach($array as $arr){
            if(!is_array($arr)) continue;
            $key = $arr[$array_key_key];
            if(is_null($array_value_key)){
                $value = $arr;
            }else{
                $value = $arr[$array_value_key];
            }
            //区别在这里
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 填充数组下标start_date和end_date的格式必须为Y-m-d H:i:s或其截断格式，例如按天格式Y-m-d，按月格式Y-m
     * step可以为day,month,year,second,minute,hour
     * start_date和end_date的传入必须与step一致
     * array的日期下标必须与step一致，例如，step为hour时，格式类似['2014-09-01 01'=>100,....]
     * @param array $array
     * @param $start_date
     * @param $end_date
     * @param string $step
     * @param int $fill_value
     * @return array
     * @throws \Exception
     */
    public static function fillArrayDateKey(array $array, $start_date, $end_date, $step='day', $fill_value=0){
        if(in_array($step, ['day', 'month', 'year'])){
            return self::fillArrayDateNumKey($array, $start_date, $end_date, $step, $fill_value);
        }elseif(in_array($step, ['second', 'minute', 'hour'])){
            return self::fillArrayDateTimeNumKey($array, $start_date, $end_date, $step, $fill_value);
        }else{
            throw new \Exception('argument step error');
        }
    }

    /**
     * 填充数组下标，下标格式为日志格式('Y-m-d')
     * @param array $array
     * @param $start_date
     * @param $end_date
     * @param $step
     * @param $fill_value
     * @throws \Exception
     * @return array
     */
    public static function fillArrayDateNumKey(array $array, $start_date, $end_date, $step='day', $fill_value=0){
        if(!strtotime($start_date) || !strtotime($end_date))
            throw new \Exception('date format error. right format is "Y-m-d"');
        if(!in_array($step, ['day', 'week', 'month', 'year']))
            throw new \Exception('step param error. it should be day, month or year');

        while(strtotime($start_date)<strtotime($end_date)){
            if(!isset($array[$start_date])){
                $array[$start_date] = $fill_value;
            }
            if($step=='day'){
                $format = 'Y-m-d';
            }elseif($step=='month'){
                $format = 'Y-m';
            }elseif($step=='year'){
                $format = 'Y';
            }
            $start_date = date($format, strtotime($start_date . " + 1 {$step}"));
        }
        ksort($array);
        return $array;
    }

    /**
     * 填充数组下标，下标格式为日志格式('Y-m-d H:i:s')
     * @param array $array
     * @param $start_time
     * @param $end_time
     * @param $step
     * @param $fill_date
     * @return array
     * @throws \Exception
     */
    public static function fillArrayDateTimeNumKey(array $array, $start_time, $end_time, $step='minute', $fill_date=0){
        if(strtolower($step)=='hour'){
            $start_time .= ':00:00';
            $end_time .= ':00:00';
        }elseif($step=='minute'){
            $start_time .= ':00';
            $end_time .= ':00';
        }
        if(!strtotime($start_time) || !strtotime($end_time))
            throw new \Exception('date format error. right format is "Y-m-d H:i:s"');
        if(!in_array($step, ['second', 'minute', 'hour']))
            throw new \Exception('step param error. it should be second, minute, or hour');

        while(strtotime($start_time)<strtotime($end_time)){
            if($step=='hour'){
                $temp_start_time = substr($start_time, 0, 13);
            }elseif($step=='minute'){
                $temp_start_time = substr($start_time, 0, 16);
            }

            if(!isset($array[$temp_start_time])){
                $array[$temp_start_time] = $fill_date;
            }
            $start_time = date("Y-m-d H:i:s", strtotime($start_time . " + 1 {$step}"));
        }
        ksort($array);
        return $array;
    }

    /**
     * 填充数组下标，例如：
     * [2=>1, 4=>1]
     * [0=>1, 1=>1, 2=>1, 3=>1, 4=>1]
     * @param array $array
     * @param $end
     * @param int $start
     * @param int $step
     * @param int $fill_value
     * @return array
     */
    public static function fillArrayNumKey(array $array, $end, $start=0, $step=1, $fill_value=0){
        for(;$start<$end;$start=$start+$step){
            if(!isset($array[$start])){
                $array[$start] = $fill_value;
            }
        }

        ksort($array);

        return $array;
    }
} 