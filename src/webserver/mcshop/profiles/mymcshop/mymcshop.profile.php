<?php
/**
* Implements hook_form_FORM_ID_alter().
*/
function mymcshop_form_install_configure_form_alter(&$form, $form_state) {
  // Use "Minecraft Website" as the default site name.
  $form['site_information']['site_name']['#default_value'] = st('Minecraft Website');
  
  // Use "admin" as the default username.
  $form['admin_account']['account']['name']['#default_value'] = 'admin';

  // Hide Update Notifications.
  $form['update_notifications']['#access'] = FALSE;
}


/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Disable the update for Commerce Kickstart.
 */
function mymcshop_form_update_manager_update_form_alter(&$form, &$form_state, $form_id) {
  if (isset($form['projects']['#options']) && isset($form['projects']['#options']['mymcshop'])) {
    if (count($form['projects']['#options']) > 1) {
      unset($form['projects']['#options']['mymcshop']);
    }
    else {
      unset($form['projects']);
      // Hide Download button if there's no other (disabled) projects to update.
      if (!isset($form['disabled_projects'])) {
        $form['actions']['#access'] = FALSE;
      }
      $form['message']['#markup'] = t('All of your projects are up to date.');
    }
  }
}
