<?php

require_once 'reorderdashboard.civix.php';

use CRM_Reorderdashboard_ExtensionUtil as E;
use \Civi\Core\Event\GenericHookEvent;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function reorderdashboard_civicrm_config(&$config) {
  _reorderdashboard_civix_civicrm_config($config);

  // Run our hook the very last, hence -255
  // https://symfony.com/doc/current/create_framework/event_dispatcher.html
  Civi::service('dispatcher')->addListener('hook_civicrm_pageRun', 'reorderdashboard_symfony_civicrm_pageRun', -255);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function reorderdashboard_civicrm_xmlMenu(&$files) {
  _reorderdashboard_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function reorderdashboard_civicrm_install() {
  _reorderdashboard_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function reorderdashboard_civicrm_postInstall() {
  _reorderdashboard_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function reorderdashboard_civicrm_uninstall() {
  _reorderdashboard_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function reorderdashboard_civicrm_enable() {
  _reorderdashboard_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function reorderdashboard_civicrm_disable() {
  _reorderdashboard_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function reorderdashboard_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reorderdashboard_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function reorderdashboard_civicrm_managed(&$entities) {
  _reorderdashboard_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function reorderdashboard_civicrm_angularModules(&$angularModules) {
  _reorderdashboard_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function reorderdashboard_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _reorderdashboard_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function reorderdashboard_civicrm_entityTypes(&$entityTypes) {
  _reorderdashboard_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_pageRun() via Symfony hook system.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function reorderdashboard_symfony_civicrm_pageRun($event, $hook) {
  // Extract args for this hook
  list($page) = $event->getHookValues();
  $pageName = get_class($page);

  if ($pageName == 'CRM_Contact_Page_View_UserDashBoard') {
    $smarty = CRM_Core_Smarty::singleton();
    $elements = $smarty->_tpl_vars['dashboardElements'];

    $order = Civi::settings()->get('reorderdashboard_order');

    if (empty($order)) {
      return;
    }

    $order = explode(',', $order);
    $new_elements = [];
    $weight = 1;

    // The user only needs to specify the elements they want to prioritise.
    while (!empty($order)) {
      $t = array_shift($order);

      foreach ($elements as $key => $el) {
        if ($el['class'] == $t) {
          $el['weight'] = $weight;
          $weight++;

          $new_elements[] = $el;
          unset($elements[$key]);
        }
      }
    }

    // Merge in any left over elements, if any.
    foreach ($elements as $el) {
      $el['weight'] = $weight;
      $weight++;

      $new_elements[] = $el;
    }

    $smarty->assign('dashboardElements', $new_elements);
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function reorderdashboard_civicrm_navigationMenu(&$menu) {
  _reorderdashboard_civix_insert_navigation_menu($menu, 'Administer/Customize Data and Screens', array(
    'label' => E::ts('Re-order User Dashboard Elements'),
    'name' => 'reorderdashboard_settings',
    'url' => 'civicrm/admin/setting/reorderdashboard',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _reorderdashboard_civix_navigationMenu($menu);
}
