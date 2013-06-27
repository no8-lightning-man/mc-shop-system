<?php
/**
 * @file
 * Install, update and uninstall functions for the mcshop module.
 */

/**
 * Implements hook_install()
 */
 
function mcshop_install() {

}

/**
 * Implements hook_uninstall().
 */
 
function mcshop_uninstall() {
  variable_del('mcshop_server_host');
  variable_del('mcshop_server_port');
  variable_del('mcshop_server_pass');
  
  variable_del('mcshop_mcconnector');
}
