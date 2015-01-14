<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 15-1-14
 * Time: 下午1:42
 */

namespace Jenner\Zebra\Tools;


class ChineseIdCard
{

    /**
     * 验证接口
     * @param $idCard
     * @return bool
     */
    public static function check($idCard)
    {
        if (strlen($idCard) == 18)
            return self::idCardAuth18($idCard);
        elseif (strlen($idCard) == 15)
            return self::idCardAuth15($idCard);
        else
            return false;
    }

    /**
     * 验证身份证前缀规则(行政区划，出生年月)
     * @param $idCard
     * @return bool
     */
    private static function checkCodeRules($idCard)
    {
        $province = substr($idCard, 0, 2);
        $city = substr($idCard, 2, 2);
        $year = intval(substr($idCard, 6, 4));
        $month = intval(substr($idCard, 10, 2));
        $day = intval(substr($idCard, 12, 2));

        //验证出生年月
        if ($year > date('Y'))
            return false;
        if ($month < 0 || $month > 12)
            return false;
        if ($day < 0 || $day > 31)
            return false;

        //国家标准GB/T 2260-2007 行政区划
        $provinceList = array(
            '11' => '北京', '12' => '天津', '13' => '河北', '14' => '山西', '15' => '内蒙古',
            '21' => '辽宁', '22' => '吉林', '23' => '黑龙江',
            '31' => '上海', '32' => '江苏', '33' => '浙江', '34' => '安徽', '35' => '福建', '36' => '江西', '37' => '山东',
            '41' => '河南', '42' => '湖北', '43' => '湖南', '44' => '广东', '45' => '广西', '46' => '海南',
            '50' => '重庆', '51' => '四川', '52' => '贵州', '53' => '云南', '54' => '西藏',
            '61' => '陕西', '62' => '甘肃', '63' => '青海', '64' => '宁夏', '65' => '新疆',
            '71' => '台湾', '81' => '香港', '82' => '台湾'
        );
        if (!isset($provinceList[$province]))
            return false;

        if ($city > '70' && $city != '90')
            return false;

        return true;
    }

    /**
     * 18位身份证号码验证
     * @param $idCard
     * @return bool
     */
    private static function idCardAuth18($idCard)
    {
        if (strlen($idCard) != 18)
            return false;

        if (!self::checkCodeRules($idCard))
            return false;

        $idCardBaseNum = substr($idCard, 0, 17);
        $idCardCheckNum = substr($idCard, 17, 1);
        $checkNum = self::getCheckNum($idCardBaseNum);
        return $idCardCheckNum === $checkNum ? true : false;
    }

    /**
     * 15位身份证号码转为18位身份证号码进行验证
     * @param $idCard
     * @return bool
     */
    private static function idCardAuth15($idCard)
    {
        if (strlen($idCard) != 15)
            return false;

        //996 997 998 999是百岁老人
        if (array_search(substr($idCard, 12, 3), array('996', '997', '998', '999')) !== false)
            $idCard = substr($idCard, 0, 6) . '18' . substr($idCard, 6, 9);
        else
            $idCard = substr($idCard, 0, 6) . '19' . substr($idCard, 6, 9);

        $idCard .= self::getCheckNum($idCard);

        return self::idCardAuth18($idCard);
    }

    /**
     * 获取身份证校验位
     * @param $idCardBaseNum
     * @return bool/string
     */
    private static function getCheckNum($idCardBaseNum)
    {
        if (strlen($idCardBaseNum) != 17)
            return false;
        //国家标准GB 11643-1999
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $checkList = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checkSum = 0;

        for ($i = 0; $i < strlen($idCardBaseNum); $i++) {
            $checkSum += substr($idCardBaseNum, $i, 1) * $factor[$i];
        }

        $mod = $checkSum % 11;
        $checkNum = $checkList[$mod];
        return $checkNum;
    }
}