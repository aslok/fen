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
  public function core() {
    // Получаем GET параметры модулей и списки значений параметров
    $modules_routes = $this->ci->all->get_once('route', array(), 'property');
    $modules_scheme = array ();
    foreach ($modules_routes as $module => $sections_arr) {
      foreach ($sections_arr as $section_name => $section_vals_arr) {
        if (!is_array($section_vals_arr) ||
            FALSE === ($section_key = array_search($section_name, $this->scheme_arr))) {
          continue;
        }
        $modules_scheme[$section_key] = '(' . implode('|', $section_vals_arr) . ')';
      }
    }
    foreach ($this->scheme_arr as $scheme_section_num => $scheme_section) {
      if (isset ($modules_scheme[$scheme_section_num])) {
        continue;
      }
      $modules_scheme[$scheme_section_num] = '([^/]*)';
    }
    ksort($modules_scheme);
    $scheme = '~(' . implode('/)?(', $modules_scheme) . '/)?~';
    preg_match($scheme, $this->ci->uri->uri_string() . '/', $matches);
    foreach ($this->scheme_arr as $section_key => $section) {
      $this->routes[$section] = isset ($matches[$section_key * 2 + 2]) ?
                                  $matches[$section_key * 2 + 2] :
                                  '';
    }
    $this->provide_get_params();
  }

  public function module_loaded($module) {
    if (empty ($this->routes)) {
      return FALSE;
    }
    $this->provide_get_params();
    return TRUE;
  }

  private function provide_get_params() {
    // Получаем значения по-умолчанию
    $modules_gets = $this->ci->all->get_once('get', array(), 'property');
    foreach ($modules_gets as $module => $sections_arr) {
      foreach ($sections_arr as $section_name => $section_val) {
        if (!isset ($this->routes[$section_name])) {
          show_error('Can not to make route to "' . $section_name .
                       '" for module ' . $module);
        }
        if (empty ($this->routes[$section_name])) {
          continue;
        }
        $modules_gets[$module][$section_name] = $this->routes[$section_name];
      }
    }
    $this->ci->all->get_fav('get', $modules_gets, 'property');
    return $modules_gets;
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
