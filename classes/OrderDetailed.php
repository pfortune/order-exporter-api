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
        $orderId = $_GET['orderid'];
        $objOrder = new Order($orderId);
        $orderDetailResponse = '';
        if (empty($objOrder->id)) {
            Json::generate(400, "error", "You have passed wrong order id.");
        } else {
            $orderDetailResponse = $this->getOrderDetails($orderId);
        }
        
        Json::generate(200, "success", "Order details fetched successfully.", $orderDetailResponse);
    }

    private function getOrderMessage($orderId)
    {
        $sql = "SELECT * FROM `"._DB_PREFIX_."message` WHERE id_order = $orderId AND private=0";
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        
        return $result;
    }
    
    private function getOrderDetails($orderId)
    {
        $orderDetails = new Order($orderId);
        
        $orderMessages = array();
        $messages = $this->getOrderMessage($orderId);

        if (!empty($messages)) {
            foreach ($messages as $message) {
                $orderMessages[] = array(
                    'message' => $message['message']
                );
            }
        }
        
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
        $customerallAddrDetails = $CustomerCoreDetail->getAddresses(Context::getContext()->language->id);
        
        if (!empty($customerallAddrDetails)) {
            $phone = $customerallAddrDetails[0]['phone'];
            if (empty($phone)) {
                $phone = $customerallAddrDetails[0]['phone_mobile'];
            }
        }
        
        $addressInfo = new AddressCore($orderDetails->id_address_delivery);
        $state = StateCore::getNameById($addressInfo->id_state);
        $countryIso = CountryCore::getIsoById($addressInfo->id_country);
        
        $resultOrderResponse = array(
            'company'   => $addressInfo->company,
            'firstname' => $CustomerCoreDetail->firstname,
            'lastname'  => $CustomerCoreDetail->lastname,
            "id" 		=> (int) $orderId,
            "date_add"	=> $orderDetails->date_add,
            'id_customer' => $orderDetails->id_customer,
            'id_carrier'=> $orderDetails->id_carrier,
            'address1'	=> $addressInfo->address1,
            'address2'	=> $addressInfo->address2,
            'city'		=> $addressInfo->city,
            'state'		=> $state,
            'postcode'	=> $addressInfo->postcode,
            'countryIso'=> $countryIso,
            'email'		=> $CustomerCoreDetail->email,
            'phone' 	=> $phone,
            'itemslist' => $itemlist,
            'message' => $orderMessages[0]['message']
        );

        return $resultOrderResponse;
    }
}
