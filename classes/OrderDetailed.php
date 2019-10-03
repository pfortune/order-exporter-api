<?php
include_once(_PS_MODULE_DIR_ . 'exporterapi/classes/Json.php');

class OrderDetailed extends ObjectModel
{
    public function __construct()
    {
        if ($_GET ['orderId'] == null) {
            Json::generate(400, "error", "You must provide a valid method.");
        } else {
            $func = $_GET ['method'];
            if (method_exists($this, $func)) {
                $this->$func();
            } else {
                Json::generate(400, "error", "Provided method not found.");
            }
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
            $orderDetailResponse = $this->getOrderDetails($orDerId);
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
            'company'			=> $addressInfo->company,
            'firstname'		=> $CustomerCoreDetail->firstname,
            'lastname'		=> $CustomerCoreDetail->lastname,
            "id" 					=> (int) $orderId,
            "date_add"		=> $orderDetails->date_add,
            'id_customer' => $orderDetails->id_customer,
            'address1'		=> $addressInfo->address1,
            'address2'		=> $addressInfo->address2,
            'city'				=> $addressInfo->city,
            'state'				=> $state,
            'postcode'		=> $addressInfo->postcode,
            'countryIso'	=> $countryIso,
            'email'				=> $CustomerCoreDetail->email,
            'phone' 			=> $phone,
            "itemslist" 	=> $itemlist
        );

        return $resultOrderResponse;
    }
}
