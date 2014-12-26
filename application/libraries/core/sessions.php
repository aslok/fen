<?php

/**
 * Файл core/session
 *
 * Реализует базовую поддержку сессий для модулей
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует базовую поддержку сессий для модулей
 *
 * Данные сессии автоматически распределяются по свойствам session модулей.
 *    В конце выполнения данные из этого свойства автоматически сохраняются
 * @core
 * @library-session
 */
class sessions {

  /** @var array Массив с данными свойств session модулей */
  public $arr;
  /** @var string Хеш код сохраненных данных, для проверки,
   *    что они изменились и нужно сохранить снова */
  private $arr_md5;
  /** @var \CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /**
   * Инициализация свойств класса
   * @library-session
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->ci->load->library('session');
    $this->arr = array();
  }

  /**
   * Загружаем данные сессии в свойство класса
   */
  public function init() {
    // Получаем массивы модулей
    $this->arr = $this->ci->stages->get('session', array(), 'property');
    // Берем сохраненные массивы модулей
    if(($saved_data = $this->ci->session->userdata('saved_data'))) {
      // Обходим полученные массивы модули
      foreach($this->arr as $data_key => &$data_val) {
        // Если нет сохранненного массива данного модуля - пропускаем его
        if(!isset($saved_data[$data_key])) {
          continue;
        }
        // Заменяем массив модуля сохраненным
        $data_val = $saved_data[$data_key];
      }
    }
    // Сохраняем контрольную сумму для кеширования
    $this->arr_md5 = md5(serialize($saved_data));
    $this->ci->stages->get('session', $this->arr, 'property');
  }

  /**
   * Сохраняет данные в сессию в момент окончания выполнения
   * @return void
   */
  public function end() {
    // Сохраняем значения сессии
    $this->arr = $this->ci->stages->get('session', array(), 'property');
    $this->save();
  }

  /**
   * Сохраняет данные в сессию из свойства класса
   * @return void
   */
  public function save() {
    if($this->arr_md5 != ($md5 = md5(serialize($this->arr)))) {
      $this->arr_md5 = $md5;
      $this->ci->session->set_userdata('saved_data', $this->arr);
    }
  }

  /**
   * Уничтожает сессию
   * @return void
   */
  public function destroy() {
    $this->ci->session->sess_destroy();
  }

}
