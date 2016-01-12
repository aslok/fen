<?php

/**
 * Файл sections/main
 *
 * Реализует загрузку модулей основной секции
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует загрузку модулей основной секции
 * @test
 */
class main {
  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  public $get;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();

    $this->get = array ('page' => FALSE);
  }

  /**
   * Хук, который запускается на последнем этапе - done
   * @return void
   */
  public function done() {
    var_dump('<pre>$this->get', $this->get);
  }
}
