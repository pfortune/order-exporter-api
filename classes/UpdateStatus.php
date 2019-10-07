<?php
include_once(_PS_MODULE_DIR_ . 'exporterapi/classes/Json.php');

class UpdateStatus extends ObjectModel
{
    public function __construct()
    {
        $postdata = json_decode(file_get_contents("php://input"));
        if (empty($postdata)) {
            Json::generate(400, "error", "There is no raw data available.");
        }
      
        if (empty($postdata->statusid)) {
            Json::generate(400, "error", "You must provide an order status id.");
        }
        if (empty($postdata->orderid)) {
            Json::generate(400, "error", "You must provide an order id.");
        }
    }
        
    private function updateOrderStatus()
    {
        $orderid 			= $postdata->orderid;
        $statusid	= $postdata->statusid;
        
        $objOrder = new Order($orderid);
        
        if (empty($objOrder->id)) {
            Json::generate(400, "error", "You have provided an incorrect order id.");
        } else {
            $orderHistory = new OrderHistoryCore();
            $orderHistory->id_order = (int)$objOrder->id;
            
            $checkStatus = $this->hasOrderStatus($statusid);
            if (empty($checkStatus)) {
                Json::generate(400, "error", "You have provided an incorrect status id.");
            }

            $orderHistory->changeIdOrderState($statusid, (int)($objOrder->id));
            $this->addInOrderHistory($orderid, $statusid);
        }
        Json::generate(200, "success", "Order status has been updated.");
    }
    
    public function addInOrderHistory($orderid, $statusid)
    {
        $sql = "INSERT INTO `"._DB_PREFIX_."order_history` (`id_employee`, `id_order`, `id_order_state`, `date_add`) 
				VALUES ('1', '$orderid', '$statusid', NOW());";
        $sql_response = Db::getInstance()->execute($sql);
    }
}
