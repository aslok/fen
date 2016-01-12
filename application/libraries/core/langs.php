<?php

/**
 * Файл core/lang
 *
 * Реализует подгрузку модулей необходимых для поддержки языков
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует подгрузку модулей необходимых для обработки запросов
 * к указанной секции
 * @core
 */
class langs {
  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  public $route;
  public $get;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->route = array ('lang' => array ('ua', 'ru', 'en'));
    $this->get = array ('lang' => 'en');
  }
}
