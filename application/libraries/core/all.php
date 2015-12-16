<?php

/**
 * Файл core/all
 *
 * Реализует функции вызова методов модулей
 * @core
 */
if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс реализует функции вызова методов модулей
 *
 * Вызывает методы модулей с необходимыми параметрами, возвращает результаты
 *
 * @core
 */
class all {

  /** @var object CI_Controller Объект для доступа к ресурсам Codeigniter */
  private $ci;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
  }

  /**
   * Выполняет указанный метод указанного модуля
   * @param string $class Название модуля
   * @param string $method Название метода
   * @param array $data Массив данных для передачи методу
   * @param bool $sic Если FALSE (или не передан) то ошибка в случае неудачи
   * @return array|bool Результат вызова или FALSE в случае неудачи
   */
  public function run($class, $method, &$data = array(), $sic = FALSE) {
    if(!isset($this->ci->mods->arr[$class])) {
      show_error('Module ' . $class . ' is not exists');
    }
    if(!in_array($class, $this->ci->mods->loaded)) {
      show_error('Module ' . $class . ' is not loaded');
    }
    if(!method_exists($this->ci->$class, $method)) {
      if(!empty($sic)) {
        return FALSE;
      }
      show_error('Method ' . $method . ' is not exists');
    }
    return $this->ci->$class->$method($data);
  }

  /**
   * Выполняет метод модулей (если он есть) и записывает массив по ссылке,
   *    возвращает этот массив возвращенных значений
   * @param array $arr Массив для возвращенных значений
   * @param string $method Название метода
   * @param array $data Данные для вызова методов
   * @param string $type Два варианта значения method|property - метод или свойство
   * @return array Массив возвращенных значений
   */
  public function to(&$arr, $method, $data = array(), $type = 'method') {
    return $this->ci->stages->get($method, $data, $type, $arr);
  }

  /**
   * Выполняет метод модулей (если он есть и ему переданы данные) и возвращает массив
   *    возвращенных значений
   * @param string $method Название метода
   * @param array $data Данные для вызова методов
   * @param string $type Два варианта значения method|property - метод или свойство
   * @param array $arr Массив для возвращенных значений
   * @return array Массив возвращенных значений
   */
  public function to_with($method, $data = array(), $type = 'method',
                          &$arr = array()) {
    return $this->ci->stages->get($method, $data, $type, $arr, TRUE);
  }

}
