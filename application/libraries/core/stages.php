<?php

/**
 * Файл core/stages
 *
 * Реализует базовую поддержку уровней выполнения
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует базовую поддержку уровней выполнения
 *
 * Вызывает методы модулей по списку
 ** core
 ** init
 ** ready
 ** check
 ** main
 ** done
 *
 * Таким образом проходят соответствующие этапы выполнения
 * @core
 */
class stages {

  /** @var int Текущий уровень выполнения */
  public $n;
  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->n = '';
  }

  /**
   * Запуск цикла уровней выполнения
   * @return void
   */
  public function mods_done() {
    foreach(array('core', 'init', 'ready', 'check', 'main', 'done') as $method) {
      $this->n = $method;
      $this->ci->all->get($method);
    }

    $this->n = '';
  }
}
