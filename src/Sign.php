<?php

namespace Xiashaung\Keloop;

CLass Sign
{
    private $dev_secret = '';

    /**
     * Sign constructor.
     *
     * @param string $dev_secret
     */
    public function __construct(string $dev_secret)
    {
        $this->dev_secret = $dev_secret;
    }


    /**
     * 获取签名
     *
     * @param array $param 密的参数数组
     * @return bool|string 生产的签名
     */
    public function make($param)
    {
        if (empty($param)) {
            return false;
        }

        // 除去待签名参数数组中的空值和签名参数
        $param = self::paraFilter($param);
        $param = self::argSort($param);
        $str = self::createLinkString($param);
        $sign = self::md5($str, $this->dev_secret);
        return $sign;
    }

    /**
     * 判断签名是否正确
     *
     * @param $params
     * @param $sign
     * @return bool
     * @throws KeLoopException
     */
    public function check($params, $sign)
    {
        $newSign = $this->make($params);
        if ($newSign !== $sign) {
            throw new KeLoopException('验签失败');
        };
        return true;
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param array $param 签名参数组
     * @return array 获取去掉空值与签名参数后的新签名参数组
     */
    private static function paraFilter($param)
    {
        $param_filter = [];
        foreach ($param as $key => $value) {
            if ($key == 'sign' || $key == 'sign_type' || $key == 'key' || (empty($value) && !is_numeric($value))) {
                continue;
            } else {
                $param_filter[$key] = $param[$key];
            }
        }
        return $param_filter;
    }

    /**
     * 对数组排序
     *
     * @param array $param 排序前的数组
     * @return mixed 排序后的数组
     */
    private static function argSort($param)
    {
        ksort($param);
        reset($param);
        return $param;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param array $param 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private static function createLinkString($param)
    {
        $arg = '';
        foreach ($param as $key => $value) {
            $arg .= $key . '=' . $value . '&';
        }
        //去掉最后一个&字符
        $arg = trim($arg, '&');
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    /**
     * 生成签名
     *
     * @param string $prestr 需要签名的字符串
     * @param string $sec 身份认证密钥(access_sec)
     * @return string 签名结果
     */
    private static function md5($prestr, $sec)
    {
        return md5($prestr . $sec);
    }
}

