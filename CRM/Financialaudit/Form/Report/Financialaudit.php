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
  class CRM_Financialaudit_Form_Report_Financialaudit extends CRM_Report_Form {
    //protected $_customGroupExtends = array('Contact','Individual','Contribution');

    static private $nscd_fid = '';
    static private $processors = array();
    static private $version = array();
    static private $financial_types = array();
    static private $prefixes = array();
    static private $contributionStatus = array();

    function __construct() {

      self::$nscd_fid = _iats_civicrm_nscd_fid();
      self::$version = _iats_civicrm_domain_info('version');
      self::$financial_types = CRM_Contribute_PseudoConstant::financialType();
      self::$prefixes = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'prefix_id');
      self::$contributionStatus = CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id');

      $params = array('version' => 3, 'sequential' => 1, 'is_test' => 0, 'return.name' => 1);
      $result = civicrm_api('PaymentProcessor', 'get', $params);
      foreach($result['values'] as $pp) {
        self::$processors[$pp['id']] = $pp['name'];
      }
      $this->_columns = array(
        'civicrm_contact' => array(
          'dao' => 'CRM_Contact_DAO_Contact',
          'order_bys' => array(
            'sort_name' => array(
              'title' => ts("Last name, First name"),
            ),
          ),
          'fields' => array(
            'id' => array(
              // 'no_display' => TRUE,
              'title' => ts('Contact ID'),
              'required' => TRUE,
            ),
            'sort_name' => array(
              'title' => ts('Sort Name'),
              'default' => TRUE,
            ),
            'contact_type' =>
              array(
                'title' => ts('Contact Type'),
                'default' => TRUE,
              ),
          ),
          'filters' =>
            array(
              'id' =>
                array('title' => ts('Contact ID'),
                  'type' => CRM_Utils_Type::T_INT,
                ),
              'sort_name' =>
                array('title' => ts('Name'),
                  'operator' => 'like',
                ),
              'contact_type' =>
                array(
                  'dao' => 'CRM_Contact_DAO_Contact',
                  'fields' =>
                    array('title' => ts('Contact Type'),
                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                      'type' => CRM_Utils_Type::T_STRING,
                    ),
                ),
            ),
        ), // civicrm_contact
        'civicrm_email' => array(
          'dao' => 'CRM_Core_DAO_Email',
          'order_bys' => array(
            'email' => array(
              'title' => ts('Email'),
            ),
          ),
          'fields' => array(
            'email' => array(
              'title' => ts('Email'),
              'no_repeat' => TRUE,
            ),
          ),
          'grouping' => 'contact-fields',
        ), // civicrm_email
        'civicrm_group' =>
          array(
            'dao' => 'CRM_Contact_DAO_Group',
            'alias' => 'cgroup',
            'filters' =>
              array(
                'gid' =>
                  array(
                    'name' => 'group_id',
                    'title' => ts('Group'),
                    'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                    'group' => TRUE,
                    'options' => CRM_Core_PseudoConstant::group(),
                  ),
              ),
        ), // civicrm_group
        'civicrm_contribution' => array(
          'dao' => 'CRM_Contribute_DAO_Contribution',
          'order_bys' => array(
            'receive_date' => array(
              'title' => ts("Received Date (most recent)"),
              'dbAlias' => "MAX(contribution_civireport.receive_date)",
            ),
          ),
          'fields' => array(
            'total_amount' => array(
              'title' => ts('Total Amount contributed'),
              'required' => TRUE,
              'statistics' =>
                array('sum' => ts('Aggregate Amount'),
                  'count' => ts('Count'),
                  'avg' => ts('Average'),
                  'max' => ts('Max'),
                ),
            ),
            'id_all' => array(
              'title' => ts('Contribution IDs - all'),
              'required' => TRUE,
              'dbAlias' => "GROUP_CONCAT(contribution_civireport.id ORDER BY contribution_civireport.receive_date ASC SEPARATOR ', ')",
            ),
            'amounts_all' => array(
              'title' => ts('Amounts - all'),
              'required' => TRUE,
              'dbAlias' => "GROUP_CONCAT(contribution_civireport.total_amount ORDER BY contribution_civireport.receive_date SEPARATOR ', ')",
            ),
            'amounts_ytd' => array(
              'title' => ts('Amount - YTD 2018'),
              'required' => TRUE,
              'dbAlias' => "SUM(CASE WHEN year(contribution_civireport.receive_date)=2018 THEN contribution_civireport.total_amount ELSE 0 END)",
            ),
            'receive_date' =>
              array(
                'title' => 'Received Date (most recent)',
                'default' => TRUE,
                'dbAlias' => "MAX(contribution_civireport.receive_date)",
              ),
            'receive_dates' =>
              array(
                'title' => 'Received Dates',
                'default' => TRUE,
                'dbAlias' => "GROUP_CONCAT(contribution_civireport.receive_date ORDER BY contribution_civireport.receive_date SEPARATOR ', ')",
              ),
          ),
          'filters' => array(
            'receive_date' =>
              array('operatorType' => CRM_Report_Form::OP_DATE),
            'financial_type_id' =>
              array('title' => ts('Financial Type'),
                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                'options' => CRM_Contribute_PseudoConstant::financialType(),
                'type' => CRM_Utils_Type::T_INT,
              ),
            'contribution_status_id' =>
              array('title' => ts('Contribution Status'),
                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
                'default' => array(1),
                'type' => CRM_Utils_Type::T_INT,
              ),
            'contribution_source' =>
              array('title' => ts('Contribution Source'),
                'operator' => 'like',
              ),
            'total_amount' => array(
              'title' => ts('Single Contribution Amount'),
              'operatorType' => CRM_Report_Form::OP_FLOAT,
              'type' => CRM_Utils_Type::T_FLOAT,
            ),
            'total_amount_sum' =>
              array('title' => ts('Aggregate Contribution Amount'),
                'type' => CRM_Report_Form::OP_INT,
                'dbAlias' => 'civicrm_contribution_total_amount_sum',
                'having' => TRUE,
              ),
            'total_amount_count' =>
              array('title' => ts('Number of Contributions - Count'),
                'type' => CRM_Report_Form::OP_INT,
                'dbAlias' => 'civicrm_contribution_total_amount_count',
                'having' => TRUE,
              ),
            'total_amount_avg' =>
              array('title' => ts('Average Contribution Amount'),
                'type' => CRM_Report_Form::OP_INT,
                'dbAlias' => 'civicrm_contribution_total_amount_avg',
                'having' => TRUE,
              ),
          ),
        ), // civicrm_contribution
      );
      $this->_tagFilter = TRUE;
      parent::__construct();
    }
    function getTemplateName() {
      return 'CRM/Report/Form.tpl' ;
    }

    function from() {
      $this->_from = "
      FROM civicrm_contact  {$this->_aliases['civicrm_contact']}
        INNER JOIN civicrm_contribution   {$this->_aliases['civicrm_contribution']}
          ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_contribution']}.contact_id";
      $this->_from .= "
      LEFT JOIN civicrm_email  {$this->_aliases['civicrm_email']}
        ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
          {$this->_aliases['civicrm_email']}.is_primary = 1 )";
    }

    function groupBy() {
       $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id ";
    }

    function alterDisplay(&$rows) {
      foreach ($rows as $rowNum => $row) {
        // convert display name to links
        if (array_key_exists('civicrm_contact_sort_name', $row) &&
          CRM_Utils_Array::value('civicrm_contact_sort_name', $rows[$rowNum]) &&
          array_key_exists('civicrm_contact_id', $row)
        ) {
          $url = CRM_Utils_System::url('civicrm/contact/view',
            'reset=1&cid=' . $row['civicrm_contact_id'],
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
          $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts('View Contact Summary for this Contact.');
        }

        // rewrite receive dates - what I really need is the amount contributed on the most recent receive date;
        if ($value = CRM_Utils_Array::value('civicrm_contribution_last_amount', $row)) {
          $last_amount = explode(',',$rows[$rowNum]['civicrm_contribution_last_amount']);
          $rows[$rowNum]['civicrm_contribution_last_amount'] = end($last_amount);
          $test = 1;
        }
      }
    }
  }

