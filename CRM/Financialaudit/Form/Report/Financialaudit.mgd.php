<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
  array (
    'name' => 'CRM_Financialaudit_Form_Report_Financialaudit',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'Financial Audit - LineItems / Contributions',
      'description' => ' Financial Audit - LineItems / Contributions',
      'class_name' => 'CRM_Financialaudit_Form_Report_Financialaudit',
      'report_url' => 'ca.civicrm.financialaudit/financialaudit',
      'component' => 'CiviContribute',
    ),
  ),
);
