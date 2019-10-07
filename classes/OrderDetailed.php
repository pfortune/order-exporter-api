<?php
include_once(_PS_MODULE_DIR_ . 'exporterapi/classes/Json.php');

class OrderDetailed extends ObjectModel
{
    public function __construct()
    {
        if ($_GET['orderid'] == null) {
            Json::generate(400, "error", "You must provide a valid order id.");
        } else {
            $this->getOrder();
        }
    }
        
    private function getOrder()
    {
        $orderId = $_GET['orderId'];
        $objOrder = new Order($orderId);
        $orderDetailResponse = '';
        if (empty($objOrder->id)) {
            Json::generate(400, "error", "You have passed wrong orderId.");
        } else {
            $orderDetailResponse = $this->getOrderDetails($orderId);
        }
        
        Json::generate(200, "success", "Order details fetched successfully.", $orderDetailResponse);
    }
    
    private function getOrderDetails($orderId)
    {
        $orderDetails = new Order($orderId);
        
        $itemlist = array();
        $OrderProductsList = $orderDetails->getOrderDetailList();
        if (!empty($OrderProductsList)) {
            foreach ($OrderProductsList as $productDetail) {
                $itemlist[] = array(
                        'product_quantity'		=> $productDetail['product_quantity'],
                        'product_reference'		=> $productDetail['product_reference'],
                );
            }
        }
        
        $CustomerCoreDetail = new CustomerCore($orderDetails->id_customer);
        $customerallAddrDetils = $CustomerCoreDetail->getAddresses(Context::getContext()->language->id);
        
        if (!empty($customerallAddrDetils)) {
            $phone = $customerallAddrDetils[0]['phone'];
            if (empty($phone)) {
                $phone = $customerallAddrDetils[0]['phone_mobile'];
            }
        }
        
        $addressInfo = new AddressCore($orderDetails->id_address_delivery);
        $state = StateCore::getNameById($addressInfo->id_state);
        $countryIso = CountryCore::getIsoById($addressInfo->id_country);
        
        $resultOrderResponse = array(
            'company'		=> $addressInfo->company,
            'firstname'		=> $CustomerCoreDetail->firstname,
            'lastname'		=> $CustomerCoreDetail->lastname,
            "id" 			=> (int) $orderId,
            "date_add"		=> $orderDetails->date_add,
            'id_customer'   => $orderDetails->id_customer,
            'address1'		=> $addressInfo->address1,
            'address2'		=> $addressInfo->address2,
            'city'			=> $addressInfo->city,
            'state'			=> $state,
            'postcode'		=> $addressInfo->postcode,
            'countryIso'	=> $countryIso,
            'email'			=> $CustomerCoreDetail->email,
            'phone' 		=> $phone,
            "itemslist" 	=> $itemlist
        );

        return $resultOrderResponse;
    }

    /**
     * Method is used to Update Order Status
     * @param id_order, id_order_status
     * @return Status
     */
    private function updateOrderStatus()
    {
        $postdata = json_decode(file_get_contents("php://input"));
        if (empty($postdata)) {
            Json::generate(400, "error", "There is no Raw data available.");
        }
        
        if (empty($postdata->id_order_status)) {
            Json::generate(400, "error", "id_order_status is empty.");
        }
        if (empty($postdata->id_order)) {
            Json::generate(400, "error", "id_order is empty.");
        }
        
        $id_order 			= $postdata->orderid;
        $id_order_status 	= $postdata->id_order_status;
        $tracking_number 	= $postdata->tracking_number;
        
        $objOrder = new Order($id_order);
        
        if (empty($objOrder->id)) {
            Json::generate(400, "error", "You have passed wrong orderId.");
        } else {
            $orderHistory = new OrderHistoryCore();
            $orderHistory->id_order = (int)$objOrder->id;
            
            $checkStatus = $this->hasOrderStatus($id_order_status);
            if (empty($checkStatus)) {
                Json::generate(400, "error", "Wrong Order Status passed.");
            }

            $orderHistory->changeIdOrderState($id_order_status, (int)($objOrder->id));
            $this->addInOrderHistory($id_order, $id_order_status);
            
            if (!empty($tracking_number) && $id_order_status == 4) {
                $this->updateTrackingNumber($id_order, $tracking_number);
            }
        }
        Json::generate(200, "success", "Orders Status updated successfully.");
    }
    
    /**
     * Method is used to Add Order Status in OrderHistory Table
     * @param id_order, id_order_status
     */
    public function addInOrderHistory($id_order, $id_order_status)
    {
        $sql = "INSERT INTO `"._DB_PREFIX_."order_history` (`id_employee`, `id_order`, `id_order_state`, `date_add`) 
				VALUES ('1', '$id_order', '$id_order_status', NOW());";
        $sql_response = Db::getInstance()->execute($sql);
    }
}
