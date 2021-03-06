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

  public $getted_mods;

  /**
   * Инициализация свойств класса
   * @return object
   */
  public function __construct() {
    $this->ci = & get_instance();
    $this->getted_mods = array ();
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

  /**
   * Выполняет метод модулей один раз (если он есть) и возвращает массив
   * возвращенных значений
   *
   * @param string $method Название метода для вызова
   * @param array $data Массив параметров передаваемых вызываемому методу
   * @param string $type Два варианта значения method|property - метод или свойство
   * @param array $arr Ссылка на массив для сохранения ответов вызванных методов
   * @param bool $fav Флаг обозначающий, что нужно вызвать только методы
   * для которых переданы данные
   * @return array Массив ответов модулей на вызов данного метода
   */
  public function get_once($method, $data = array(), $type = 'method',
                           &$arr = array(), $fav = FALSE) {
    return $this->get($method, $data, $type, $arr, $fav, TRUE);
  }

  /**
   * Выполняет метод модулей (если он есть и ему переданы данные) и
   * возвращает массив возвращенных значений
   *
   * @param string $method Название метода для вызова
   * @param array $data Массив параметров передаваемых вызываемому методу
   * @param string $type Два варианта значения method|property - метод или свойство
   * @param array $arr Ссылка на массив для сохранения ответов вызванных методов
   * @param bool $once Флаг обозначающий, что нужно вызвать методы модулей
   * только по одному разу за все время выполнения
   * @return array Массив ответов модулей на вызов данного метода
   */
  public function get_fav($method, $data = array(), $type = 'method',
                           &$arr = array(), $once = FALSE) {
    return $this->get($method, $data, $type, $arr, $once);
  }

  /**
   * Выполняет метод модулей (если он есть) и возвращает массив
   * возвращенных значений
   *
   * @param string $method Название метода для вызова
   * @param array $data Массив параметров передаваемых вызываемому методу
   * @param string $type Два варианта значения method|property - метод или свойство
   * @param array $arr Ссылка на массив для сохранения ответов вызванных методов
   * @param bool $fav Флаг обозначающий, что нужно вызвать только методы
   * для которых переданы данные
   * @param bool $once Флаг обозначающий, что нужно вызвать методы модулей
   * только по одному разу за все время выполнения
   * @return array Массив ответов модулей на вызов данного метода
   */
  public function get($method, $data = array(), $type = 'method',
                      &$arr = array(), $fav = FALSE, $once = FALSE) {
    // Массив модулей которые уже были вызваны
    $stage_done = array();
    // Номер текущего вызываемого модуля
    $cur_pos = 0;
    // Повторяем до тех пор, пока количество вызванных модулей не совпадет с
    // количеством загруженных модулей
    while(count($stage_done) !== count($this->ci->mods->loaded)) {
      // Повторяем до тех пор пока существует модуль с данной позицией
      while(isset($this->ci->mods->loaded[$cur_pos])) {
        // Название текущего модуля
        $module = $this->ci->mods->loaded[$cur_pos];
        // Если модуль уже вызывался - пропускаем
        if(in_array($module, $stage_done)) {
          // Переходим к следующей позиции
          $cur_pos++;
          continue;
        }
        // Проверяем зависимости выполняя методы модулей $method . '_dep'
        if(!$this->check_dependencies($module, $method, $stage_done)) {
          // Если зависимости требуют разрешения
          // Если данный модуль на последней позиции - показываем ошибку
          if($this->ci->mods->loaded[$cur_pos] === end($this->ci->mods->loaded)) {
            show_error('Wrong ' . $module . ' dep on collect ' . $method);
          }
          // Если позиция не последняя - помещаем модуль на последнюю позицию
          $this->ci->mods->loaded[] = $this->ci->mods->loaded[$cur_pos];
          unset($this->ci->mods->loaded[$cur_pos]);
          // Не меняя позиции переходим к следующему модулю
          continue;
        }
        // Если с зависимостями всё ок - добавляем модуль в список выполненных
        $stage_done[] = $module;
        // Проверяем доступа на запуск методов модуля
        if(!$this->check_perms($module)) {
          // Если доступов нет, переходим к следующему модулю
          $cur_pos++;
          continue;
        }
        // Если необходимо обойти только те модули, для которых переданы данные
        if($fav && !isset($data[$module]) && !isset($data['*'])) {
          // Если данные для данного модуля не переданы - переходим к следующему
          $cur_pos++;
          continue;
        }
        // Если необходимо обойти только те модули, которые не обошли ранее
        if($once &&
           isset ($this->getted_mods[$module]) &&
           isset ($this->getted_mods[$module][$method]) &&
           isset ($this->getted_mods[$module][$method][$type])) {
          // Если обошли ранее - переходим к следующему
          $cur_pos++;
          continue;
        }
        // Есть два варианта - доступ к свойству и к методу
        switch($type) {
          // Свойство
          case 'property':
            // Если свойство существует
            if(property_exists($this->ci->$module, $method)) {
              // Если для модуля переданы данные
              if(isset($data[$module])) {
                // Сохраняем данные в свойство
                $this->ci->$module->$method = $data[$module];
              }else {
                // Читаем данные из свойства и сохраняем в ответ
                $arr[$module] = $this->ci->$module->$method;
                if ($once) {
                  if (!isset ($this->getted_mods[$module])) {
                    $this->getted_mods[$module] = array ();
                  }
                  if (!isset ($this->getted_mods[$module][$method])) {
                    $this->getted_mods[$module][$method] = array ();
                  }
                  if (!isset ($this->getted_mods[$module][$method][$type])) {
                    $this->getted_mods[$module][$method][$type] = TRUE;
                  }
                }
              }
            }
            break;
          // Метод
          case 'method':
            // Если метод существует
            if(method_exists($this->ci->$module, $method)) {
              // Запускаем метод модуля - результат сохраняем в ответ
              // Если для модуля были переданы данные
              $arr[$module] = $this->ci->$module->$method(isset($data[$module]) ?
                  // Вызываем метод с параметром - этими данными
                  $data[$module] :
                  // Иначе - если данные были переданы всем модулям
                  (isset($data['*']) ?
                    // Вызываем метод с параметром - этими данными
                    $data['*'] :
                    // Иначе вызываем метод с параметром - пустым массивом
                    array()));
              if ($once) {
                if (!isset ($this->getted_mods[$module])) {
                  $this->getted_mods[$module] = array ();
                }
                if (!isset ($this->getted_mods[$module][$method])) {
                  $this->getted_mods[$module][$method] = array ();
                }
                if (!isset ($this->getted_mods[$module][$method][$type])) {
                  $this->getted_mods[$module][$method][$type] = TRUE;
                }
              }
            }
            break;
          // В другом случае показываем ошибку
          default:
            show_error('Wrong type on collect ' . $method);
        }
        // Переходим к следующему модулю
        $cur_pos++;
      }
    }
    // Возвращаем ответ
    return $arr;
  }

  /**
   * Проверяет порядок выполнения метода модуля,
   *    возможно необходимо отложить выполнение
   *
   * @param string $module Название модуля
   * @param string $method Название метода, который необходимо выполнить.
   *    Его зависимости возвращает метод с названием $method . '_dep'
   * @param array $stage_done Массив названий методов выполненных на данном этапе
   * @return boolean Если с зависимостей нет или они удовлетворены - TRUE
   */
  private function check_dependencies($module, $method, $stage_done) {
    $method_dep = $method . '_dep';
    if(!in_array($module, $this->ci->mods->loaded)) {
      show_error('Module ' . $module . ' is not loaded');
    }
    if(!method_exists($this->ci->$module, $method_dep)) {
      return TRUE;
    }
    $deps = $this->ci->$module->$method_dep();
    //dump($module, $method_dep, $deps);
    if(empty($deps)) {
      return TRUE;
    }
    if(!is_array($deps)) {
      $deps = array($deps);
    }
    foreach($deps as $dep) {
      if(!in_array($dep, $stage_done)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Проверяет доступы к выполнению методов модуля
   *
   * Для проверки вызывается цикл рекурсивной проверки свойств необходимых
   *    методов, в случае наличия доступов возвращает TRUE
   *
   * @param string $module Название модуля для проверки доступов
   * @return boolean В случае наличия доступов возвращает TRUE
   */
  private function check_perms($module) {
    if(!in_array($module, $this->ci->mods->loaded)) {
      show_error('Module ' . $module . ' is not loaded');
    }
    if(!isset($this->ci->$module->perms)) {
      return TRUE;
    }
    if(FALSE === $this->ci->$module->perms) {
      return FALSE;
    }
    foreach($this->ci->$module->perms as $perms_mod => $parms) {
      if(!in_array($perms_mod, $this->ci->mods->loaded)) {
        show_error('Module ' . $perms_mod . ' is not loaded');
      }
      $vars = get_object_vars($this->ci->$perms_mod);
      return $this->array_diff_only_exists_vals($vars, $parms);
    }
    return TRUE;
  }

  /**
   * Рекурсивно сравнивает два массива в поисках разницы
   *
   * Отсутствие элементов второго массива разницей не является,
   *    сравнение происходит только с существующими значениями
   *
   * @param array $vars Массив для поиска отличий
   * @param array $parms Контрольный массив с значениями для проверки
   * @return boolean В случае отсутствия разницы возвращает TRUE
   */
  public function array_diff_only_exists_vals($vars, $parms) {
    foreach($parms as $parm_key => $parm) {
      if(!isset($vars[$parm_key])) {
        show_error('Property ' . $parm_key . ' is not exists');
      }
      if(is_array($parm) && is_array($vars[$parm_key])) {
        return $this->array_diff_only_exists_vals($vars[$parm_key], $parms[$parm_key]);
      }elseif(is_array($parm)) {
        return FALSE;
      }elseif($vars[$parm_key] !== $parm) {
        return FALSE;
      }
    }
    return TRUE;
  }


}
