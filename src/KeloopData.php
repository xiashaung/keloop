<?php


namespace Xiashaung\Keloop;


use Xiashuang\Keloop\KeloopException;

class KeloopData
{
     private $data = [];

     public function __construct(array $data)
     {
         $this->data = $data;
         if (!$this->isSuccess()) {
             throw new KeloopException($data['message']);
         }
     }

    /**
     * 是否成功
     *
     * @return bool
     */
     public function isSuccess()
     {
         return $this->data['code'] == 200;
     }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    
}