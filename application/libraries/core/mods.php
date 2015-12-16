<?php

/**
 * Файл core/mods
 *
 * Реализует базовую поддержку модулей
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует базовую работу с модулями
 *
 * Загружает модули по требованию. Составляет списки доступных и загруженных
 *    модулей
 *
 * @core
 * @helper-directory
 */
class mods {

  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /** @var array Массив модулей доступных для загрузки */
  public $arr;

  /** @var array Массив загруженных модулей */
  public $loaded;

  /** @var string Название каталога модулей ядра */
  private $core_folder;

  /** @var string Название стартового метода модулей ядра */
  private $init_method;

  /** @var string Суффикс для названий выполненных методов */
  private $done_suffix;

  /**
   * Инициализация свойств класса
   *
   * Для работы с каталогами подключается хелпер directory
   *
   * @helper-directory
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();

    $this->ci->load->helper(array('directory'));

    $this->arr = array();
    $this->loaded = array();
    $this->core_folder = 'core' . DIRECTORY_SEPARATOR;
    $this->init_method = 'index';
    $this->done_suffix = '_done';
  }

  /**
   * Выполняет инициализацию модулей и загружает модули ядра
   *
   * Запускает инициализацию списка доступных модулей и загружает его
   *    в свойство $this->arr. Автоматически загружает модули ядра из каталога
   *    libraries/core/
   * @return void
   */
  public function start() {
    $this->get_modules_list();
    $this->loaded[] = get_class();
    $this->load_core_mods();
    $this->all_run($this->init_method . $this->done_suffix);
  }

  /**
   * Выполняет загрузку модуля или модулей из $this->arr
   *
   * @param array|string $modules Название (или массив названий) модуля
   *    который необходимо загрузить
   * @return void
   */
  public function load($modules) {
    if(!is_array($modules)) {
      $modules = array($modules);
    }
    foreach($modules as $module) {
      if(isset($this->arr[$module])) {
        $this->load_module($module);
      }else {
        show_error('Module ' . $module . ' is not exists');
      }
    }
  }

  /**
   * Ищет в $this->arr модули ядра и загружает их
   * @return void
   */
  private function load_core_mods() {
    foreach(array_filter($this->arr, array($this, 'core_mods_filter')) as
        $mod_name => $mod) {
      $this->load_module($mod_name, $this->init_method);
    }
  }

  /**
   * Ищет в $this->arr и загружает модуль
   *
   * @param string $module Название модуля который необходимо загрузить
   * @param string $method Название метода модуля который необходимо запустить после загрузки модуля
   * @return bool|mixed Результат загрузки
   *    или результат выполнения стартового метода
   */
  private function load_module($module, $method = FALSE) {
    if(!isset($this->arr[$module])) {
      show_error('Module ' . $module . ' is not exists');
    }
    if(in_array($module, $this->loaded)) {
      return FALSE;
    }
    $this->ci->load->library($this->arr[$module], NULL, $module);
    if(!isset($this->ci->$module)) {
      show_error('Can not to load module ' . $module . ' from ' . $this->arr[$module]);
    }
    $this->loaded[] = $module;
    return FALSE !== $method ? $this->run($module, $method) : TRUE;
  }

  /**
   * Выполняет метод загруженных модулей (если он есть)
   *
   * @param string $method Название метода для вызова
   * @return void
   */
  private function all_run($method) {
    foreach($this->loaded as $module) {
      $this->run($module, $method);
    }
  }

  /**
   * Выполняет метод модуля (если он есть)
   *
   * @param string $module Название модуля для вызова
   * @param string $method Название метода для вызова
   * @return bool|mixed Результат выполнения метода или FALSE если метода нет
   */
  private function run($module, $method) {
    if(in_array($module, $this->loaded) &&
       !empty($method) &&
       method_exists($this->ci->$module, $method)){
      return $this->ci->$module->$method();
    }
    return NULL;
  }

  /**
   * Заполняет свойство $this->arr данными о файлах
   * @return void
   */
  private function get_modules_list() {
    $directory = FCPATH . APPPATH . 'libraries' . DIRECTORY_SEPARATOR;
    array_walk($this->mods_files_list(directory_map($directory)),
                                                    array($this, 'mods_files_to_libs'));
  }

  /**
   * Конвертирует элементы массива файлов directory_map в $this->arr
   *
   * Массив $this->arr имеет формат
   * <pre>
   * array(2) {
   *    ["mods"]=> string(9) "core/mods"
   *    ["newclass"]=> string(8) "newclass"
   * }
   * </pre>
   * Где "mods" название, а "core/mods" путь к файлу без расширения.
   * Контролируется уникальность названий.
   *
   * @param string $item Название файла с расширением
   * @return void
   */
  private function mods_files_to_libs($item) {
    $item_path_arr = pathinfo($item);
    $item_path =
      $item_path_arr['dirname'] !== '.' ?
        ($item_path_arr['dirname'] . DIRECTORY_SEPARATOR) :
        '';
    $item_no_ext = $item_path . $item_path_arr['filename'];
    if(isset($this->arr[$item_path_arr['filename']])) {
      show_error('Incorrect module name ' . $item_no_ext .
        ', module with name "' . $item_path_arr['filename'] .
        '" allrady exists ./' . $this->arr[$item_path_arr['filename']]);
    }
    $this->arr[$item_path_arr['filename']] = $item_no_ext;
  }

  /**
   * Рекурсивно обходит каталоги, собирает массив файлов
   *
   * @param array $directory Древовидный массив каталогов и файлов
   * @return array Названия и пути к файлам модулей
   */
  private function mods_files_list($directory) {
    $mods = array();
    foreach($directory as $dirname => $dir) {
      if(!is_array($dir)) {
        continue;
      }
      $subdir_files = $this->mods_files_list($dir);
      $subdir_mods = array_filter($subdir_files,
                                  array($this, 'mods_files_filter'));
      foreach($subdir_mods as $mod) {
        $mods[] = $dirname . DIRECTORY_SEPARATOR . $mod;
      }
    }
    $dir_mods = array_filter($directory, array($this, 'mods_files_filter'));
    return array_merge($mods, $dir_mods);
  }

  /**
   * Проверяет, соответствует ли имя файла шаблону имени модуля
   *
   * Имя файла не должно быть массивом и должно заканчиваться на .php
   * @param string $filename Имя файла для проверки
   * @return bool Результат проверки
   */
  private function mods_files_filter($filename) {
    if(is_array($filename)) {
      return FALSE;
    }
    return 'php' === pathinfo($filename, PATHINFO_EXTENSION);
  }

  /**
   * Проверяет, соответствует ли имя файла шаблону модуля ядра
   *
   * Имя файла должно начинаться с core/ (core_folder)
   * @param string $mod Имя файла модуля для проверки
   * @return bool Результат проверки
   */
  private function core_mods_filter($mod) {
    return 0 === strpos($mod, $this->core_folder);
  }

}
