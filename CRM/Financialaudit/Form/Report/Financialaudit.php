<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 * $Id$
 *
 */
  class CRM_Financialaudit_Form_Report_Financialaudit extends CRM_Extendedreport_Form_Report_ExtendedReport {
    protected $_customGroupExtends = array('Contribution');

    protected $_baseTable = 'civicrm_line_item';

    protected $_aclTable = 'civicrm_contact';

    /**
     * Class constructor.
     */
    public function __construct() {
      $this->_columns
        = $this->getColumns('Contact', array('order_by' => TRUE, 'fields_defaults' => array('sort_name', 'id')))
        + $this->getColumns('Contribution', array('order_by' => TRUE, 'fields_defaults' => array('total_amount', 'trxn_id', 'receive_date', 'id', 'contribution_status_id')))
        + $this->getColumns('LineItem', array('order_by' => TRUE, 'fields_defaults' => array('financial_type_id', 'line_total', 'tax_amount')));
      parent::__construct();
    }

    function preProcess() {
      parent::preProcess();
    }

    function select() {
      parent::select();
    }

    /**
     * Select from clauses to use.
     *
     * (from those advertised using $this->getAvailableJoins()).
     *
     * @return array
     */
    public function fromClauses() {
      return array(
        'contribution_from_lineItem',
        'contact_from_contribution',
      );
    }

    function groupBy() {
      parent::groupBy();

    }

    function orderBy() {
      parent::orderBy();
    }

    /**
     * @param $rows
     *
     * @return mixed
     */
    function statistics(&$rows) {
      return parent::statistics($rows);
    }

    function postProcess() {
      parent::postProcess();
    }

    /**
     * Alter rows display.
     *
     * @param $rows
     */
    public function alterDisplay(&$rows) {
      parent::alterDisplay($rows);

      foreach ($rows as $rowNum => $row) {

        if (array_key_exists('civicrm_contact_civicrm_contact_sort_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_civicrm_contact_sort_name', $rows[$rowNum]) &&
        array_key_exists('civicrm_contact_civicrm_contact_contact_id', $row)
        ) {
          $url = CRM_Utils_System::url('civicrm/contact/view',
            'reset=1&cid=' . $row['civicrm_contact_civicrm_contact_contact_id'],
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_contact_civicrm_contact_sort_name_link'] = $url;
          $rows[$rowNum]['civicrm_contact_civicrm_contact_sort_name_hover'] = ts('View Contact Summary for this Contact.');
        }

        $rows[$rowNum]['civicrm_contribution_contribution_total_amount_sum'] = $rows[$rowNum]['civicrm_contribution_contribution_total_amount_sum'] / $rows[$rowNum]['civicrm_contribution_contribution_total_amount_count'];

      }

    }
  }
