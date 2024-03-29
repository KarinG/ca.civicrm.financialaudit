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

  protected $_baseTable = 'civicrm_line_item';
  protected $_aclTable = 'civicrm_contact';

  /**
    * Class constructor.
    */
  public function __construct() {
    $this->_columns
      = $this->getColumns('Contact', array('fields_defaults' => array('sort_name', 'id')))
      + $this->getColumns('Contribution', array('fields_defaults' => array('total_amount', 'trxn_id', 'receive_date', 'id', 'contribution_status_id')))
      + $this->getColumns('LineItem', array('fields_defaults' => array('financial_type_id', 'line_total', 'tax_amount')));
    parent::__construct();
  }

  /**
   * Select from clauses to use.
   *
   * (from ExtendedReports).
   *
   * @return array
   */

  public function fromClauses() :array {
    return [
      'contribution_from_lineItem',
      'contact_from_contribution',
    ];
  }

  function orderBy() {
    parent::orderBy();
    // If I add this under groupBy - ExtendedReport thinks we want to groupBy line_item.id
    $this->_groupByArray = ['civicrm_contribution_id' => $this->_aliases['civicrm_contribution'] . '.id'];
  }

  /**
   * @param $rows
   *
   * @return mixed
   */
  function statistics(&$rows) {
    return parent::statistics($rows);
  }

  /**
   * Alter rows display.
   *
   * @param $rows
   */
  public function alterDisplay(&$rows): void {

    // Change labels:
    $this->_columnHeaders['civicrm_contact_civicrm_contact_sort_name']['title'] = "Contact Name";
    $this->_columnHeaders['civicrm_contribution_contribution_total_amount_count']['title'] = "#Line Items";
    $this->_columnHeaders['civicrm_delta']['title'] = "Delta with Line Items";

    // Jitendra's fix - no longer merged in ExtendedReports: https://github.com/eileenmcnaughton/nz.co.fuzion.extendedreport/pull/98/files
    // ROLLUP adds an additional row (CONCAT) - so we need to slice by 1 row
    $lastIndex = key(array_slice($rows, -1, 1, TRUE));
    foreach ($rows[$lastIndex] as $key => &$val) {
      $val = NULL;
    }

    foreach ($rows as $rowNum => $row) {

      if (array_key_exists('civicrm_contact_civicrm_contact_sort_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_civicrm_contact_sort_name', $rows[$rowNum]) &&
        array_key_exists('civicrm_contact_civicrm_contact_contact_id', $row)
        )
      {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['civicrm_contact_civicrm_contact_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_civicrm_contact_sort_name_hover'] = ts('View Contact Summary for this Contact.');
      }

      $rows[$rowNum]['civicrm_contribution_contribution_total_amount_sum'] = $rows[$rowNum]['civicrm_contribution_contribution_total_amount_sum'] / $rows[$rowNum]['civicrm_contribution_contribution_total_amount_count'];

      $rows[$rowNum]['civicrm_delta'] = $rows[$rowNum]['civicrm_contribution_contribution_total_amount_sum'] - ($rows[$rowNum]['civicrm_line_item_line_item_line_total_sum'] + $rows[$rowNum]['civicrm_line_item_line_item_tax_amount_sum']);

      if (abs($rows[$rowNum]['civicrm_delta']) > 0.001) {
        $url = CRM_Utils_System::url('civicrm/contact/view/contribution',
          'reset=1&id=' . $row['civicrm_contribution_contribution_id'] . '&cid=' . $row['civicrm_contact_civicrm_contact_contact_id'] . '&action=view&context=contribution&selectedChild=contribute',
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contribution_contribution_id_link'] = $url;
        $rows[$rowNum]['civicrm_contribution_contribution_id_hover'] = ts('View Contribution.');
        $rows[$rowNum]['civicrm_delta_link'] = $url;
        $rows[$rowNum]['civicrm_delta_hover'] = ts('View Contribution.');
      }
    }

    $this->rollupRow = array_shift($rows);
    foreach ($this->rollupRow as $key => &$value){
      $value += array_sum(array_column($rows, $key));
    }
    unset($value);

    $this->rollupRow['civicrm_contact_civicrm_contact_contact_id'] = "Totals";
    $this->rollupRow['civicrm_contact_civicrm_contact_sort_name'] = NULL;
    $this->rollupRow['civicrm_contribution_contribution_id'] = NULL;
    $this->rollupRow['civicrm_contribution_contribution_contribution_status_id'] = NULL;
    $this->rollupRow['civicrm_contribution_contribution_trxn_id'] = NULL;
    $this->rollupRow['civicrm_contribution_contribution_receive_date'] = NULL;
    $this->rollupRow['civicrm_line_item_line_item_financial_type_id'] = NULL;

    $this->assign('grandStat', $this->rollupRow);

    parent::alterDisplay($rows);
  }
}
