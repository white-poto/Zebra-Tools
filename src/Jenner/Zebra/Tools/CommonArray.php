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
class CommonArray
{
    /**
     * 根据列的Key获取一个二维数组中某一列的值，作为一维数组返回
     * @param $column
     * @param $array
     * @return array
     */
    public static function columnValue($column, $array)
    {
        $result = [];
        $array = (array)$array;
        foreach ($array as $value) {
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
    public static function fieldValue($filed, $object)
    {
        $result = [];
        $object = (object)$object;
        foreach ($object as $value) {
            $result[] = isset($value->$filed) ? false : $value->$filed;
        }
        return $result;
    }

    /**
     * 过滤一个二维数组的列，只保留指定的列
     * @param $array
     * @param $fields
     * @return array
     */
    public static function filterField($array, $fields)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = self::filterColumn($value, $fields);
        }

        return $result;
    }

    /**
     * 过滤一个一位数组的columns，只保留指定的columns
     * @param $array
     * @param $columns
     * @return array
     */
    public static function filterColumn($array, $columns)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (in_array($key, $columns)) {
                $result[$key] = $value;
            }
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
    public static function objectToArray($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
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
    public static function tableToArrayMapping(array $array, $array_key_key, $array_value_key = null)
    {
        $result = [];
        foreach ($array as $arr) {
            if (!is_array($arr)) continue;
            $key = $arr[$array_key_key];
            if (is_null($array_value_key)) {
                $value = $arr;
            } else {
                $value = $arr[$array_value_key];
            }
            //区别在这里
            $result[$key][] = $value;
        }

        return $result;
    }

    /**
     * 将一个二维数组，以其中一列为KEY，一列为VALUE，返回一个一维数组
     * @param array $array
     * @param null $column_key
     * @param $index_key
     * @throws \Exception
     * @return array
     */
    public static function arrayColumn($array, $column_key, $index_key = null)
    {
        if(!is_array($array) && !($array instanceof \ArrayAccess))
            throw new \Exception('Argument 1 passed to Jenner\Zebra\Tools\CommonArray::arrayColumn() must be of the type array');

        if (function_exists('array_column ')) {
            return array_column($array, $column_key, $index_key);
        }

        $result = [];
        foreach ($array as $arr) {

            if (!is_array($arr) && !($arr instanceof \ArrayAccess)) continue;

            if (is_null($column_key)) {
                $value = $arr;
            } else {
                $value = $arr[$column_key];
            }

            if (!is_null($index_key)) {
                $key = $arr[$index_key];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }

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
    public static function fillArrayDateKey(array $array, $start_date, $end_date, $step = 'day', $fill_value = 0)
    {
        if (in_array($step, ['day', 'month', 'year'])) {
            return self::fillArrayDateNumKey($array, $start_date, $end_date, $step, $fill_value);
        } elseif (in_array($step, ['second', 'minute', 'ten_minute', 'hour'])) {
            return self::fillArrayDateTimeNumKey($array, $start_date, $end_date, $step, $fill_value);
        } elseif ($step == 'week') {
            return self::fillArrayWeekNumKey($array, $start_date, $end_date, $fill_value);
        } else {
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
    public static function fillArrayDateNumKey(array $array, $start_date, $end_date, $step = 'day', $fill_value = 0)
    {
        if (!strtotime($start_date) || !strtotime($end_date))
            throw new \Exception('date format error. right format is "Y-m-d"');
        if (!in_array($step, ['day', 'week', 'month', 'year']))
            throw new \Exception('step param error. it should be day, month or year');

        while (strtotime($start_date) <= strtotime($end_date)) {
            if (!isset($array[$start_date])) {
                $array[$start_date] = $fill_value;
            }
            if ($step == 'day') {
                $format = 'Y-m-d';
            } elseif ($step == 'month') {
                $format = 'Y-m';
            } elseif ($step == 'year') {
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
    public static function fillArrayDateTimeNumKey(array $array, $start_time, $end_time, $step = 'minute', $fill_date = 0)
    {
        if (strtolower($step) == 'hour') {
            $start_time .= ':00:00';
            $end_time .= ':59:59';
        } elseif ($step == 'minute') {
            $start_time .= ':00';
            $end_time .= ':59';
        } elseif ($step = 'ten_minute') {
            $start_time .= '0:00';
            $end_time .= '9:59';
        }

        if (!strtotime($start_time) || !strtotime($end_time))
            throw new \Exception('date format error. right format is "Y-m-d H:i:s"');
        if (!in_array($step, ['second', 'minute', 'ten_minute', 'hour']))
            throw new \Exception('step param error. it should be second, minute, ten_minute, or hour');

        while (strtotime($start_time) <= strtotime($end_time)) {
            if ($step == 'hour') {
                $temp_start_time = substr($start_time, 0, 13);
            } elseif ($step == 'minute') {
                $temp_start_time = substr($start_time, 0, 16);
            } elseif ($step == 'ten_minute') {
                $temp_start_time = substr($start_time, 0, 15);
            }

            if (!isset($array[$temp_start_time])) {
                $array[$temp_start_time] = $fill_date;
            }
            if ($step == 'ten_minute') {
                $start_time = date("Y-m-d H:i:s", strtotime($start_time . " + 10 minute"));
            } else {
                $start_time = date("Y-m-d H:i:s", strtotime($start_time . " + 1 {$step}"));
            }

        }
        ksort($array);
        return $array;
    }

    /**
     * 填充星期数组下标，下标格式为日志格式('Y-m-d H:i:s')
     * @param array $array 数组下标必须为week of year(一年中的第几周)
     * @param $start_time 开始日期
     * @param $end_time 结束日期
     * @param int $fill_value
     * @throws \Exception
     * @return array
     */
    public static function fillArrayWeekNumKey(array $array, $start_time, $end_time, $fill_value = 0)
    {
        if (!strtotime($start_time) || !strtotime($end_time))
            throw new \Exception('date format error. right format is "Y-m-d H:i:s"');

        while (strtotime($start_time) <= strtotime($end_time)) {
            $week = intval(date('W', strtotime($start_time)));
            if (!isset($array[$week])) {
                $array[$week] = $fill_value;
            }
            $start_time = date("Y-m-d H:i:s", strtotime($start_time . " + 1 Week"));
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
    public static function fillArrayNumKey(array $array, $end, $start = 0, $step = 1, $fill_value = 0)
    {
        for (; $start < $end; $start = $start + $step) {
            if (!isset($array[$start])) {
                $array[$start] = $fill_value;
            }
        }

        ksort($array);

        return $array;
    }

    /**
     * 递归转换数组编码
     * @param $array
     * @param $to_encoding
     * @param $from_encoding
     * @return mixed
     */
    public static function convertEncoding($array, $to_encoding, $from_encoding)
    {
        array_walk_recursive($array, function (&$item, $key) use ($to_encoding, $from_encoding) {
            $item = mb_convert_encoding($item, $to_encoding, $from_encoding);
        });
        return $array;
    }

    /**
     * 合并两个二维数组的列
     * @param $array_1
     * @param $array_2
     * @return array
     */
    public static function columnMergeByKey($array_1, $array_2)
    {
        $result = [];
        foreach ($array_1 as $key => $value) {
            foreach ($array_2 as $key_2 => $value_2) {
                if ($key == $key_2) {
                    $result[$key][] = $value;
                    $result[$key][] = $value_2;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 根据两个二维数组的一位下标，进行数组合并
     * @param $array_1
     * @param $array_2
     * @return array
     */
    public static function mergeByKey($array_1, $array_2)
    {
        $result = [];
        foreach ($array_1 as $key_1 => $value_1) {
            foreach ($array_2 as $key_2 => $value_2) {
                if ($key_1 == $key_2) {
                    $result[$key_1] = array_merge($value_1, $value_2);
                    break;
                }
            }
        }

        return $result;
    }


    /**
     * 将有父子关系的二维数组，转换为外围数组包含子级元素的形式，常用场景类似于mysql中的一张表，存储了数据的层级关系
     * 可以使用该函数将含有层级关系的数组转换为明确层级关系的多维数组，只支持一个级别，不能递归
     * @param $array
     * @param string $parent_key 父亲标识字段
     * @param string $primary_key 主键标识字段
     * @param string $sub_key 生成子元素时的下标
     * @return array
     */
    public static function arrayToFiliation($array, $parent_key = 'pid', $primary_key = 'id', $sub_key = 'sub')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (!isset($value[$parent_key]) || empty($value[$parent_key])) {
                $result[] = $value;
                unset($array[$key]);
            }
        }

        foreach ($result as $key => $parent_value) {
            foreach ($array as $value) {
                if ($value[$parent_key] == $parent_value[$primary_key]) {
                    if(!isset($result[$key][$sub_key])) $result[$key][$sub_key] = [];
                    $result[$key][$sub_key][] = $value;
                }
            }
        }

        return $result;
    }


    /**
     * Groups an array by a given key. Any additional keys will be used for grouping
     * the next set of sub-arrays.
     *
     * @author Jake Zatecky
     *
     * @param array $arr The array to have grouping performed on.
     * @param mixed $key The key to group or split by.
     *
     * @return array
     */
    static function groupBy($arr, $key)
    {
        if (!is_array($arr)) {
            trigger_error("array_group_by(): The first argument should be an array", E_USER_ERROR);
        }
        if (!is_string($key) && !is_int($key) && !is_float($key)) {
            trigger_error("array_group_by(): The key should be a string or an integer", E_USER_ERROR);
        }
        // Load the new array, splitting by the target key
        $grouped = array();
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params = array_merge(array($value), array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array("\\Jenner\\Zebra\\Tools\\CommonArray::groupBy", $params);
            }
        }
        return $grouped;
    }

    /**
     * 一位数组求平均值
     * @param $array
     * @return float
     */
    public static function arrayAverage($array){
        $sum = 0;
        array_walk($array, function(&$item) use(&$sum){
            $item = floatval($item);
            $sum += $item;
        });

        return $sum / count($array);
    }

    /**
     * 获取数组第一个值
     * @param $array
     * @return mixed
     */
    public static function arrayFirstValue($array)
    {
        foreach ($array as $value) {
            return $value;
        }
    }
}

if (!function_exists('array_column_recursive')) {
    /**
     * Returns the values recursively from columns of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                         a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                         the returned array. This value may be the integer key
     *                         of the column, or it may be the string key name.
     *
     * @return array
     */
    function array_column_recursive($input = NULL, $columnKey = NULL, $indexKey = NULL)
    {

        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column_recursive() expects at least 2 parameters, {$argc} given", E_USER_WARNING);

            return NULL;
        }
        if (!is_array($params[0])) {
            // Because we call back to this function, check if call was made by self to
            // prevent debug/error output for recursiveness :)
            $callers = debug_backtrace();
            if ($callers[1]['function'] != 'array_column_recursive') {
                trigger_error('array_column_recursive() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            }

            return NULL;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== NULL
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column_recursive(): The column key should be either a string or an integer', E_USER_WARNING);

            return FALSE;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column_recursive(): The index key should be either a string or an integer', E_USER_WARNING);

            return FALSE;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== NULL) ? (string)$params[1] : NULL;
        $paramsIndexKey = NULL;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = NULL;
            $keySet = $valueSet = FALSE;
            if ($paramsIndexKey !== NULL && array_key_exists($paramsIndexKey, $row)) {
                $keySet = TRUE;
                $key = (string)$row[$paramsIndexKey];
            }
            if ($paramsColumnKey === NULL) {
                $valueSet = TRUE;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = TRUE;
                $value = $row[$paramsColumnKey];
            }

            $possibleValue = array_column_recursive($row, $paramsColumnKey, $paramsIndexKey);
            if ($possibleValue) {
                $resultArray = array_merge($possibleValue, $resultArray);
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }
}