<?php

/**
 * @desc Check system for bot comptability and set needed inital values.
 */
function CheckSystem() {  
  echo "Testing bot and system ...\n";
  
  // system capabilities check
  
  // php version check
  echo "    PHP version: ".phpversion()."\n";
  if (phpversion() < 5) {
     exit("   Fatal error: PHP version must be 5 or higher\n");
  }
  
  $extensions = get_loaded_extensions();
  
  // iconv exetnsion check
  echo "    Checking for iconv extension... \n";
  if (!in_array('iconv', $extensions)) {
      if ( !function_exists('libiconv') ) {
          echo "    [ Warning ] no iconv extension found\n";
      } else {
          function iconv($input_encoding, $output_encoding, $string) {
              return libiconv($input_encoding, $output_encoding, $string);
          }
          echo "    [ Warning ] iconv() replaced with libiconv()\n";
      }
  } else {
      echo "    iconv OK\n";
  }
  
  // mbstring extension check
  echo "    Checking for mbstring extension... \n";
  if ( !in_array('mbstring', $extensions) ) {
      exit("    [ Fatal error ] no mb_string extension found");
  } else {
      echo "    mbstring OK\n";
  }

  // script time limit check
  $time = ini_get('max_execution_time');
  set_time_limit(0);
  echo "    Bot time limit check... \n";
  if (ini_get('max_execution_time') > 0) {
     exit("[ Fatal error ] script time limit must be equal 0\n");
  } else {
     echo "    max_execution_time=0. OK\n";
  }

  echo "    Checking timezone settings... \n";
  $timezone = ini_get('date.timezone');
  if (!$timezone) {
	  echo "    [ Notice ] date.timezone in php.ini is not set. Using 'Europe/Moscow' option.\n";
	  ini_set('date.timezone', 'Europe/Moscow');
  } else {
	echo "    date.timezone=$timezone. OK\n";
  }
  echo "Test OK\n";
}
 
