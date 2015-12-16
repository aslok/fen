<?php

/**
 * Файл config/hooks
 *
 * Hooks
 *
 * This file lets you define "hooks" to extend CI without hacking the core
 * files.  Please see the user guide for info:
 *
 *	http://codeigniter.com/user_guide/general/hooks.html
 * @config
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$hook['display_override'] =
  array('class' => 'Compress',
        'function' => 'output',
        'filename' => 'compress.php',
        'filepath' => 'hooks');

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */