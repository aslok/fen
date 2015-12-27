<?php

/**
 * Файл core/in
 *
 * Реализует прием POST и GET запросов
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует прием POST и GET запросов
 *
 * @core
 */
class in {

  /** @var array Массив полученных GET параметров */
  public $get_arr;
  /** @var array Массив полученных POST параметров */
  public $post_arr;
  /** @var array Массив параметров распределенных по модулям */
  public $routes;
  /** @var string Строка-схема строки GET с названиями секций URI */
  public $scheme;
  /** @var array Массив с названиями секций URI */
  public $scheme_arr;

  /** @var array Массив для хранения POST параметров в сессии */
  public $session;

  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->session = array ();
    $this->get_arr = array ();
    foreach ($this->ci->uri->segment_array() as $uri_param) {
      $this->get_arr[] = $this->ci->security->xss_clean($uri_param);
    }
    $this->post_arr = array ();
    $this->routes = array ();
    $this->scheme = 'lang/section/page/action/params';
    $this->scheme_arr = explode('/', $this->scheme);
  }

  /**
   * Распределяем полученные GET параметры по модулям
   * @return void
   */
  public function ready() {
    // Получаем GET параметры модулей и списки значений параметров
    $modules_routes = $this->ci->stages->get('route', array(), 'property');
    // Получаем значения по-умолчанию
    $modules_gets = $this->ci->stages->get('get', array(), 'property');
    // Обогощаем GET параметры модулей именами параметров указанных
    // в массиве значений по-умолчанию
    foreach ($modules_gets as $module => $module_section) {
      if (!is_array($module_section)) {
        continue;
      }
      foreach ($module_section as $section_name => $section_values) {
        if (!isset ($modules_routes[$module][$section_name])) {
          $modules_routes[$module][$section_name] = FALSE;
        }
      }
    }
    // Стек полученных GET параметров для распределения между модулями
    $get_arr = $this->get_arr;
    // Обходим массив-схему строки GET, $scheme_part - текущая секция для поиска
    $scheme_length = count($this->scheme_arr);
    $get_next = TRUE;
    foreach ($this->scheme_arr as $scheme_part) {
      $scheme_length--;
      // Если нужно получить свежее значение для поиска
      if ($get_next) {
        // Если схемы не достаточно для распределения - в последней секции будут
        // оставшиеся параметры в виде массива
        if (empty ($scheme_length)) {
          $tmp_arr = array ();
          while (!is_null($get = array_shift($get_arr))) {
            $tmp_arr[] = $get;
          }
          $get_arr = array ($tmp_arr);
        }
        // Если больше ничего не передано
        if (is_null($get = array_shift($get_arr))) {
          // Заканчиваем поиски
          break;
        }
        $get_next = FALSE;
      }
      // Обходим секции полученные из свойств модулей
      foreach ($modules_routes as $module => $route_section) {
        if (!is_array($route_section)) {
          continue;
        }
        // Ищем текущую секцию среди секций модуля
        foreach ($route_section as $section_name => $section_values) {
          // Если найден модуль которому нужна текущая секция
          if ($scheme_part == $section_name) {
            // Если мы знаем варианты значения секции - проверяем
            if (is_array($section_values) &&
                FALSE === array_search($get, $section_values, TRUE)) {
              continue;
            }
            // Сохраняем текущую секцию в массив параметров
            if (!isset ($this->routes[$module])) {
              $this->routes[$module] = array ();
            }
            $this->routes[$module][$section_name] = $get;
            // Обходим секции полученные из свойств других модулей
            foreach ($modules_routes as $other_module => $other_route_section) {
              if ($module == $other_module ||
                  !is_array($other_route_section)) {
                continue;
              }
              // Ищем текущую секцию среди секций других модулей
              foreach ($other_route_section as $other_section_name => $other_section_values) {
                // Если найден другой модуль которому нужна текущая секция
                if ($other_section_name == $section_name &&
                    (!isset ($this->routes[$other_module]) ||
                     !isset ($this->routes[$other_module][$section_name]))) {
                  // Сохраняем текущую секцию в массив параметров другого модуля
                  if (!isset ($this->routes[$other_module])) {
                    $this->routes[$other_module] = array ();
                  }
                  $this->routes[$other_module][$section_name] = $get;
                }
              }
            }
            $get_next = TRUE;
          }
        }
      }
    }
    // Если невозможно найти назначение исходя из текущей схемы
    if (!$get_next) {
      show_error('Can not to make route to "' . $get . '"');
    }
    $gets = array_replace_recursive($modules_gets, $this->routes);
    // Если GET параметер необходимый модулю небыл получен,
    // но он присутствовал в схеме - присваиваем ему FALSE и передаем модулю
    // Обходим секции полученные из свойств модулей
    foreach ($modules_routes as $module => $route_section) {
      if (!is_array($route_section)) {
        continue;
      }
      // Проверяем наличие текущей секции
      foreach ($route_section as $section_name => $section_values) {
        if (!isset ($gets[$module][$section_name])) {
          $gets[$module][$section_name] = FALSE;
        }
      }
    }
    $this->ci->stages->get('get', $gets, 'property', $tmp = array (), TRUE);
  }

  /**
   * Передаем свои данные - сохраненные в сессии
   * @return array
   */
  public function data() {
    return $this->session;
  }

  /**
   * Удаляем свои данные - сохраненные в сессии
   * @return void
   */
  public function forget_post() {
    $this->session = array ();
  }

}
