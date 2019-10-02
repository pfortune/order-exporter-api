<?php
include_once (PS_MODULE_DIR_ . 'exporterapi/classes/Json.php');

class OrderDetailed extends ObjectModel {
  
  public function __construct() {
    if ($_GET['method'] == null) {
      Json::generate(400, 'error', 'You must provide a valid method.');
    } else {
      $func = $_GET['method'];
      if(method_exists($this, $func)) {
        $this->func();
      } else {
        Json::generate(400, 'error', 'Provided method not found.');
      }
    }
  }
  
}