<?php

if(!defined('_PS_VERSION_')) {
  exit;
}

include_once (PS_MODULE_DIR_ . 'exporterapi/classes/OrderDetailed.php');

class exporterapi extends Module 
{
  public function __construct() 
  {
    $this->name = 'exporterapi';
    $this->tab = 'others';
    $this->version = '1.0.0';
    $this->author = 'Peter Fortune';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = [
      'min' => '1.7',
      'max' => '1.7.6'
    ];
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('Exporter API');
    $this->description = $this->l('Add a custom Web Service endpoint.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

  }

  public function hookAddWebserviceResources($resources) {
    $added_resources['orderdetailed'] = [
      'description' => 'Export Detailed Orders',
      'class' => 'OrderDetailed'
    ];

    return $added_resources;
  }

  public function install() {
    if (!parent::install() || !$this->registerHook('addWebserviceResources')) {
      return false;
    }

    return true;
  }

  public function uninstall() {
    return parent::uninstall();
  }
}