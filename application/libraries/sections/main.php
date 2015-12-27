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

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
  }
}
