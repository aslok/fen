<?php

/**
 * Файл hooks/compress
 *
 * Реализует минимизацию HTML перед выводом в браузер
 * @hooks
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс для минимизация перед выводом в браузер
 */
class Compress {
  /**
   * Метод для минимизации HTML
   *
   * @return void
   */
  function output()
  {
    $CI =& get_instance();
    $CI->output->set_output(preg_replace('/(<!DOCTYPE[^>]+>)\s*/',
                                         "\\1\n",
                                         preg_replace(array('/\n/',
                                                            '/\>[^\S ]+/s',
                                                            '/[^\S ]+\</s',
                                                            '/(\s)+/s'),
                                                      array(' ',
                                                            '>',
                                                            '<',
                                                            '\\1'),
                                                      $CI->output->get_output())));
    $CI->output->_display();
  }
}
