<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-11-7
 * Time: 上午9:41
 */

namespace Jenner\Zebra\Tools;


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
} 