<?php
/**
 * Файл controllers/modules.php
 *
 * Заглушка для использования системы модулей
 */

if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Стандартный класс Codeigniter расширяемый от CI_Controller
 *
 * Заглушка для использования системы модулей вместо одного контроллера
 */
class Modules extends CI_Controller {

  /**
   * Стандратный метод по-умолчанию index()
   *
   * Загружает "core/mods" и выполняет "mods->start()"
   * @return void
   */
  public function index() {
    $this->load->library('core/mods');
    $this->mods->start();
  }

}
