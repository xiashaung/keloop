<?php


namespace Xiashaung\Keloop;


use Xiashuang\Keloop\KeloopException;

class Keloop
{
    /**
     * @var string
     * 开发者key
     */
    protected $dev_key = '';

    /**
     * @var string
     * 开发者秘钥
     */
    protected $dev_secret = '';

    /**
     * @var string
     * 配送团队的token
     */
    protected $team_token = '';

    /**
     * @var Sign
     */
    protected $sign;

    /*
     * 接口调用的固定版本
     */
    const VERSION = 1;

    /**
     * 接口的基础url
     */
    const BASE_API_URL = 'https://open.keloop.cn/';

    /**
     * Base constructor.
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $this->checkConfig($config);
        $this->dev_key = $config['dev_key'];
        $this->dev_secret = $config['dev_secret'];
        $this->team_token = $config['team_token'];
        $this->sign = new Sign($this->dev_secret);
    }


    /**
     * 获取团队信息
     *
     * @return KeloopData
     * @throws KeloopException
     */
    public function getTeamInfo()
    {
        return $this->get('open/team/getTeamInfo', []);
    }

    /**
     * 获取该平台下所有的商户
     *
     * @return KeloopData
     * @throws KeloopException
     */
    public function getMerchants()
    {
        return $this->get('open/merchant/getMerchants', []);
    }

    /**
     * 获取所有的配送员
     *
     * @return KeloopData
     * @throws KeloopException
     */
    public function getDrivers()
    {
        return $this->get('open/courier/getCouriers', []);
    }

    /**
     * 获取配送费
     *
     * @param int $shop_id 门店id
     * @param string $addressGps string 收货地址的火星坐标
     * @param string $get_tag 用户收货地址的火星坐标
     * @param int $payFee 原始配送费
     * @param int $orderPrice 订单总价
     * @return mixed 返回计算的配送费
     * @throws KeloopException
     */
    public function getPostFee(int $shop_id,string $addressGps,string $get_tag, $payFee = 0, $orderPrice = 0)
    {
        $data = $this->get('open/order/getFee', [
            'shop_id' => $shop_id,
            'get_tag' => $get_tag,
            'customer_tag' => $addressGps,
            'pay_fee' => $payFee,
            'order_price' => $orderPrice,
        ]);
        return $data->pay_fee;
    }


    /**
     * 配置检查
     *
     * @param $config
     * @throws \Exception
     */
    private function checkConfig($config)
    {
        if (!$config['dev_key']) {
            throw new KeloopException("dev_key not exists");
        }
        if (!$config['dev_secret']) {
            throw new KeloopException("dev_secret not exists");
        }
        if (!$config['team_token']) {
            throw new KeloopException("team_token not exists");
        }
    }


    /**
     * generate ticket
     *
     * @return string
     */
    protected function genTicket()
    {
        if (function_exists('com_create_guid')) {
            $uuid = trim(com_create_guid(), '{}');
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
        }
        return strtoupper($uuid);
    }

    /**
     * 请求接口的参数封装
     *
     * @param array $param
     * @return array
     */
    protected function requestData($param = [])
    {

        $data = [
            'version' => self::VERSION,
            'timestamp' => time(),
            'ticket' => $this->genTicket(),
            'team_token' => $this->team_token,
            'dev_key' => $this->dev_key,
            'body' => json_encode($param)
        ];
        $data['sign'] = $this->sign->make($data);
        return $data;
    }

    /**
     * @param $path string 访问路径
     * @param array $param 参数
     * @param int $timeout 超时时间
     * @return array
     * @throws KeloopException
     */
    public function get($path, $param = [], $timeout = 3)
    {
        $curl = curl_init(self::BASE_API_URL . $path . '?' . http_build_query($this->requestData($param)));
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        return (new KeloopData(json_decode($response, true)))->getData();
    }

    /**
     * @param $path string 访问路径
     * @param array $param 参数
     * @param int $timeout 超时时间
     * @return array
     * @throws KeloopException
     */
    public function post($path, $param = [], $timeout = 3)
    {
        $url = self::BASE_API_URL . $path;
        //编码特殊字符
        $p = http_build_query($this->requestData($param));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置header
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 运行cURL，请求网页
        $data = curl_exec($curl);
        curl_close($curl);
        return (new KeloopData(json_decode($data, true)))->getData();
    }

    /**
     * @return Sign
     */
    public function getSign()
    {
        return $this->sign;
    }
}