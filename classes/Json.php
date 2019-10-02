<?php

class Json {
  public static function generate($code, $status, $message, $data = array()) {
    $response = array(
      'code',
      'status',
      'message'
    );

    if(!empty($data)) {
      $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    die(http_response_code($code));
  }
}