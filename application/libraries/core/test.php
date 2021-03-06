<?php

/**
 * Файл core/test
 *
 * Файл-шаблон для тестирования кода
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует тестовые функции
 * @test
 */
class test {
  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  public $route;
  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->route = array ('section' => array ('test'));
  }

  /**
   * Хук, который запускается на последнем этапе - done
   * @return void
   */
  public function done() {
    var_dump('<pre>$this->ci->in->routes, $this->ci->sections->get', $this->ci->in->routes, $this->ci->sections->get);
  }
}
