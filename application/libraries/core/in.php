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
 * Настройки рутинга контролируются свойством $this->scheme и
 * свойствами route модулей подключенных на этапе "core".
 * Разобранные параметры хранятся в свойстве $this->routes и
 * автоматически заменяют соответствующие параметры других модулей
 * в их свойствах get
 * @core
 */
class in {

  /** @var array Массив полученных GET значений */
  public $get_arr;
  /** @var array Массив полученных POST параметров */
  public $post_arr;
  /** @var array Массив GET параметров */
  public $routes;
  /** @var array Массив GET параметров распределенных по модулям */
  public $modules_routes;
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
    $this->modules_routes = array ();
    $this->scheme = 'lang/section/page/action/params';
    $this->scheme_arr = explode('/', $this->scheme);
  }

  /**
   * Распределяем полученные GET параметры исходя из текущей схемы
   * $this->scheme и данных полученных из свойств модулей 'route'
   * @return void
   */
  public function core() {
    // Получаем GET параметры модулей и списки значений параметров
    $this->modules_routes = $this->ci->all->get_once('route', array(), 'property');
    // Формируем массив возможных значений параметров
    $modules_scheme_arr = array ();
    // Обходим каждый модуль полученных списков значений параметров
    foreach ($this->modules_routes as $module => $sections_arr) {
      // Обходим каждый из полученных списков значений параметров
      foreach ($sections_arr as $section_name => $section_vals_arr) {
        // Если это не список или если его наименование отсутствует в схеме
        if (!is_array($section_vals_arr) ||
            FALSE === ($section_key = array_search($section_name, $this->scheme_arr))) {
          // Пропускаем
          continue;
        }
        // Создаем соответствующий массив, если его нет
        if (!isset ($modules_scheme_arr[$section_key])) {
          $modules_scheme_arr[$section_key] = array ();
        }
        // Добавляем список в массив
        $modules_scheme_arr[$section_key] =
          array_merge($modules_scheme_arr[$section_key], $section_vals_arr);
      }
    }
    // Создаем массив с секциями схемы для поиска и разбора GET параметров
    $modules_scheme = array ();
    foreach ($modules_scheme_arr as $section_key => $section_vals_arr) {
      $modules_scheme[$section_key] = '(' . implode('|', $section_vals_arr) . ')';
    }
    // Добавляем неопределенныые секции, для остальных параметров
    foreach ($this->scheme_arr as $scheme_section_num => $scheme_section) {
      if (isset ($modules_scheme[$scheme_section_num])) {
        continue;
      }
      $modules_scheme[$scheme_section_num] = '([^/]*)';
    }
    // Сортируем по порядку схемы
    ksort($modules_scheme);
    // Регулярное выражение для поиска и разбора
    $scheme = '~(' . implode('/)?(', $modules_scheme) . '/)?~';
    preg_match($scheme, $this->ci->uri->uri_string() . '/', $matches);
    // Генерируем массив параметров
    foreach ($this->scheme_arr as $section_key => $section) {
      $this->routes[$section] = isset ($matches[$section_key * 2 + 2]) ?
                                  $matches[$section_key * 2 + 2] :
                                  '';
    }
    // Предоставляем параметры модулям
    $this->provide_get_params();
  }

  /**
   * Хук, который запускается в момент загрузки нового модуля
   * @return bool Ждет выполнения хука на этапе core, возвращает FALSE
   * После выполнения хука начинает возвращать TRUE
   */
  public function module_loaded($module) {
    if (empty ($this->routes)) {
      return FALSE;
    }
    // Предоставляем параметры модулям
    $this->provide_get_params();
    return TRUE;
  }

  /**
   * Предоставляем модулям, которым это раньше не предлагалось,
   * текущие GET параметры. Если параметр не был передан, но
   * модуль имеет значение по-умолчанию, то оно будет оставлено
   * @return array Параметры переданные модулям (для отладки)
   */
  private function provide_get_params() {
    // Получаем значения по-умолчанию
    $modules_gets = $this->ci->all->get_once('get', array(), 'property');
    // Обходим каждый модуль полученных значений по-умолчанию
    foreach ($modules_gets as $module => $sections_arr) {
      // Обходим каждое из полученных значений по-умолчанию
      foreach ($sections_arr as $section_name => $section_val) {
        // Выдаем ошибку если нет такой секции
        if (!isset ($this->routes[$section_name])) {
          show_error('Can not to make route to "' . $section_name .
                       '" for module ' . $module);
        }
        // Если у нас нет данных для этой секции - пропускаем
        if (empty ($this->routes[$section_name])) {
          continue;
        }
        // Если у нас данные не для этого модуля - сохраняем пустое значение
        if (isset ($this->modules_routes[$module]) &&
            isset ($this->modules_routes[$module][$section_name]) &&
            FALSE === array_search($this->routes[$section_name],
                                   $this->modules_routes[$module][$section_name])) {
          $modules_gets[$module][$section_name] = '';
        } else {
          // Сохраняем полученные GET параметры в элемент секции
          $modules_gets[$module][$section_name] = $this->routes[$section_name];
        }
      }
    }
    // Передаем данные модулям
    $this->ci->all->get_fav('get', $modules_gets, 'property');
    // Возвращаем параметры переданные модулям (для отладки)
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
