<?php

use CRM_Reorderdashboard_ExtensionUtil as E;

return [
  'reorderdashboard_order' => [
    'group_name' => 'domain',
    'group' => 'reorderdashboard',
    'name' => 'reorderdashboard_order',
    'type' => 'String',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Dashboard element order'),
    'description' => '',
    'help_text' => '',
    'quick_form_type' => 'Element',
    'html_type' => 'Text',
  ],
];
