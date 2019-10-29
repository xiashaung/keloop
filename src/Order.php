<?php


namespace Xiashaung\Keloop;


use Xiashuang\Keloop\KeloopException;

/**
 * 单独实现订单相关的接口
 *
 * Class Order
 * @package Xiashaung\Keloop
 */
class Order  extends Keloop
{
    /**
     * @param array $orderInfo
     * @return array
     * @throws KeloopException
     */
    public function create(array $orderInfo)
    {
        return  $this->post('open/order/createOrder', $orderInfo);
    }


    /**
     * 获取配送员的火星坐标
     *
     * @param string $keloop_order_sn   快跑者订单号
     * @return string
     * @throws KeloopException
     */
    public function getDriverGps(string $keloop_order_sn)
    {
        $data = $this->get('open/order/getCourierTag', ['trade_no' => $keloop_order_sn]);
        return $data->latitude . ',' . $data->longitude;
    }

    /**
     * 取消订单
     *
     * @param $keloop_order_sn  string 快跑者订单号
     * @param string $reason 取消订单的理由
     * @return array
     * @throws KeloopException
     */
    public function cancelOrder($keloop_order_sn,$reason = '')
    {
        return $this->post('open/order/cancelOrder', ['trade_no' => $keloop_order_sn, 'reason' => $reason]);
    }
}