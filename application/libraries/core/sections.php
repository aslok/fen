<?php

/**
 * Файл core/sections
 *
 * Реализует подгрузку модулей необходимых для обработки запросов
 * к указанной секции
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует подгрузку модулей необходимых для обработки запросов
 * к указанной секции
 * @core
 */
class sections {
  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /** @var string Название каталога модулей секций */
  private $sections_folder;

  public $route;
  public $get;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->sections_folder = 'sections';
    $this->route = array ('section' => array ('main', 'admin'));
    $this->get = array ('section' => 'main');
  }

  /**
   * Хук, который запускается во время подгрузки модулей необходимых
   * для обработки запроса. Загружает модуль текущей секции сайта
   * @return void
   */
  public function init() {
    foreach (array_filter($this->ci->mods->arr,
                          array($this, 'sections_mods_filter')) as
             $module => $path) {
      if ($this->get['section'] == $module) {
        $this->ci->mods->load($module);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Проверяет, соответствует ли имя файла шаблону модуля секций
   *
   * Имя файла должно начинаться с sections/ (sections_folder)
   * @param string $mod Имя файла модуля для проверки
   * @return bool Результат проверки
   */
  private function sections_mods_filter($mod) {
    return 0 === strpos($mod, $this->sections_folder);
  }
}
