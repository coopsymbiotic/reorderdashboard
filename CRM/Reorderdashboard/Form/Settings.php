<?php

use CRM_Reorderdashboard_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Reorderdashboard_Form_Settings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'reorderdashboard');
  private $_submittedValues = array();
  private $_settings = array();

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
    }
    return $settings['values'];
  }

  public function buildQuickForm() {
    $settings = $this->getFormSettings();
    $descriptions = [];

    $elements = CRM_Reorderdashboard_Utils::getDashboardElements();
    $html = '<ul><li>' . implode('</li><li>', $elements) . '</li></ul>';
    $descriptions['reorderdashboard_order'] = E::ts('Available items are: %1', [1 => $html]) . ' ' . E::ts('Enter by order of preference, separated by comma (no spaces). Any element not listed will be shown at the end of the list.');

    // The above resets the page title (to 'Dashboard'), set it back.
    CRM_Utils_System::setTitle(E::ts('Re-order User Dashboard Elements'));

    // Lazyness
    Civi::resources()->addStyle('#reorderdashboard_order { width: 1000px; }');

    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];

        if ($add == 'addElement') {
          $attributes = CRM_Utils_Array::value('html_attributes', $setting, []);
          $this->$add($setting['html_type'], $name, $setting['title'], $attributes);
        }
        elseif ($setting['html_type'] == 'Date') {
          $attributes = CRM_Utils_Array::value('html_attributes', $setting, []);
          $this->add('text', $name, $setting['title'], $attributes);

          $e = $this->getElement($name);
          $e->setAttribute('type', 'date');
        }
        elseif ($setting['html_type'] == 'Select') {
          $optionValues = [
            '' => ts('- select -'),
          ];

          if (!empty($setting['pseudoconstant'])) {
            if (!empty($setting['pseudoconstant']['optionGroupName'])) {
              $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
            }
            elseif (!empty($setting['pseudoconstant']['api_entity'])) {
              if (!empty($setting['pseudoconstant']['api_field'])) {
                $t = civicrm_api3($setting['pseudoconstant']['api_entity'], 'get', [
                  'option.limit' => 0,
                ])['values'];

                foreach ($t as $key => $val) {
                  $field = $setting['pseudoconstant']['api_field'];
                  $optionValues[$key] = $val[$field];
                }
              }
              elseif (!empty($setting['pseudoconstant']['api_getoptions'])) {
                $optionValues += civicrm_api3($setting['pseudoconstant']['api_entity'], 'getoptions', [
                  'field' => $setting['pseudoconstant']['api_getoptions'],
                  'option.limit' => 0,
                ])['values'];
              }
            }
          }

          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
        }
        elseif ($setting['html_type'] == 'Checkbox') {
          $this->addElement('checkbox', $setting['name'], NULL);
        }
        else {
          $this->$add($name, $setting['title']);
        }

        if (!empty($setting['description'])) {
          $descriptions[$setting['name']] = $setting['description'];
        }
      }

      $this->assign("elementDescriptions", $descriptions);
    }

    $this->addButtons(array(
      array (
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    $settings = $this->getFormSettings();
    $values = array_intersect_key($values, $settings);
    civicrm_api3('setting', 'create', $values);

    parent::postProcess();
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
