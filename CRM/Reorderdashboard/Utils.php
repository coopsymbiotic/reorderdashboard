<?php

class CRM_Reorderdashboard_Utils {

  public static function getDashboardElements() {
    $page = new CRM_Contact_Page_View_UserDashBoard();
    $page->buildUserDashBoard();

    // Invoke the pageRun hook.
    // Hopefully extension authors don't do very whacky things when
    // customizing the pageRun for UserDashBoard.
    \CRM_Utils_Hook::pageRun($page);

    $smarty = CRM_Core_Smarty::singleton();
    $dashboardElements = $smarty->_tpl_vars['dashboardElements'];

    $css_names = [];

    foreach ($dashboardElements as $el) {
      $css_names[] = $el['class'];
    }

    return $css_names;
  }

}
