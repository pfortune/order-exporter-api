<?php
include_once(_PS_MODULE_DIR_ . 'exporterapi/classes/Json.php');

class OrderDetailed extends ObjectModel
{
    public function __construct()
    {
        if ($_GET ['method'] == null) {
            Json::generate(400, "error", "You have to pass method in api.");
        } else {
            $func = $_GET ['method'];
            if (method_exists($this, $func)) {
                $this->$func();
            } else {
                Json::generate(400, "error", "Called method not found.");
            }
        }
    }
}
