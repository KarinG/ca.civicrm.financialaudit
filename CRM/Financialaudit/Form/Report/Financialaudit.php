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
    protected $_customGroupExtends = array('Contact','Individual','Contribution');

    static private $nscd_fid = '';
    static private $processors = array();
    static private $version = array();
    static private $financial_types = array();
    static private $prefixes = array();
    static private $contributionStatus = array();

    function __construct() {

      self::$nscd_fid = _iats_civicrm_nscd_fid();
      self::$version = _iats_civicrm_domain_info('version');
      self::$financial_types = (self::$version[0] <= 4 && self::$version[1] <= 2) ? array() : CRM_Contribute_PseudoConstant::financialType();
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
            'nick_name' => array(
              'title' => ts('Nick Name'),
              'default' => TRUE,
            ),
            'addressee_display' =>
              array('title' => ts('Addressee'),
                'no_repeat' => TRUE,
                'default' => TRUE,
              ),
            'contact_type' =>
              array(
                'required' => TRUE,
                'default' => TRUE,
              ),
            'contact_sub_type' =>
              array(
                'required' => TRUE,
                'default' => TRUE,
              ),
            'prefix_id' => array(
              'title' => ts('Prefix'),
              'default' => TRUE,
            ),
            'first_name' => array(
              'title' => ts('First Name'),
              'default' => TRUE,
            ),
            'middle_name' => array(
              'title' => ts('Middle Name'),
              'default' => TRUE,
            ),
            'last_name' => array(
              'title' => ts('Last Name'),
              'default' => TRUE,
            ),
            'is_deceased' =>
              array(
                'default' => TRUE,
              ),
            'do_not_mail' =>
              array(
                'default' => TRUE,
              ),
            'do_not_email' =>
              array(
                'default' => TRUE,
              ),
            'do_not_trade' =>
              array(
                'default' => TRUE,
              ),
            'do_not_phone' =>
              array(
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
                array('title' => ts('Donor Name'),
                  'operator' => 'like',
                ),
              'is_deceased' =>
                array('title' => ts('Is Deceased'),
                  'operatorType' => CRM_Report_Form::OP_SELECT,
                  'options' => array('0' => 'No', '1' => 'Yes'),
                  'type' => CRM_Utils_Type::T_STRING,
                ),
              'do_not_mail' =>
                array('title' => ts('Do not Mail'),
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => array('0' => 'No', '1' => 'Yes'),
                  'type' => CRM_Utils_Type::T_STRING,
                ),
              'do_not_email' =>
                array('title' => ts('Do not EMail'),
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => array('0' => 'No', '1' => 'Yes'),
                  'type' => CRM_Utils_Type::T_STRING,
                ),
              'do_not_trade' =>
                array('title' => ts('Do not Trade'),
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => array('0' => 'No', '1' => 'Yes'),
                  'type' => CRM_Utils_Type::T_STRING,
                ),
              'do_not_phone' =>
                array('title' => ts('Do not Phone'),
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => array('0' => 'No', '1' => 'Yes'),
                  'type' => CRM_Utils_Type::T_STRING,
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
              'contact_sub_type' =>
                array(
                  'dao' => 'CRM_Contact_DAO_Contact',
                  'fields' =>
                    array('title' => ts('Contact Subtype'),
                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                      'type' => CRM_Utils_Type::T_STRING,
                    ),
                ),
            ),
        ),
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
        ),
        'civicrm_membership' =>
          array(
            'dao' => 'CRM_Member_DAO_Membership',
            'fields' =>
              array(
                'membership_type_id' => array(
                  'title' => 'Membership Type',
                  'required' => TRUE,
                  'no_repeat' => TRUE,
                ),
                'membership_start_date' => array('title' => ts('Membership Start Date'),
                  'default' => TRUE,
                ),
                'membership_end_date' => array('title' => ts('Membership End Date'),
                  'default' => TRUE,
                ),
                'join_date' => array('title' => ts('Membership Join Date'),
                  'default' => TRUE,
                ),
              ),
            'filters' => array(
              'join_date' =>
                array('operatorType' => CRM_Report_Form::OP_DATE),
              'membership_start_date' =>
                array('operatorType' => CRM_Report_Form::OP_DATE),
              'membership_end_date' =>
                array('operatorType' => CRM_Report_Form::OP_DATE),
              'owner_membership_id' =>
                array('title' => ts('Membership Owner ID'),
                  'operatorType' => CRM_Report_Form::OP_INT,
                ),
              'tid' =>
                array(
                  'name' => 'membership_type_id',
                  'title' => ts('Membership Types'),
                  'type' => CRM_Utils_Type::T_INT,
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => CRM_Member_PseudoConstant::membershipType(),
                ),
            ),
            'grouping' => 'member-fields',
          ),
        'civicrm_membership_status' =>
          array(
            'dao' => 'CRM_Member_DAO_MembershipStatus',
            'alias' => 'mem_status',
            'fields' =>
              array('name' => array('title' => ts('Membership Status'),
                'default' => TRUE,
              ),
              ),
            'filters' => array(
              'sid' =>
                array(
                  'name' => 'id',
                  'title' => ts('Status'),
                  'type' => CRM_Utils_Type::T_INT,
                  'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                  'options' => CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label'),
                ),
            ),
            'grouping' => 'member-fields',
          ),
        'civicrm_address' => array(
          'dao' => 'CRM_Core_DAO_Address',
          'fields' => array(
            'location_type_id' => array(
              'title' => ts('Location Type ID'),
              'default' => TRUE,
            ),
            'street_address' => array(
              'title' => ts('Address'),
              'default' => TRUE,
            ),
            'supplemental_address_1' => array(
              'title' => ts('Supplementary Address Field 1'),
              'default' => TRUE,
            ),
            'supplemental_address_2' => array(
              'title' => ts('Supplementary Address Field 2'),
              'default' => TRUE,
            ),
            'city' => array(
              'title' => 'City',
              'default' => TRUE,
            ),
            'state_province_id' => array(
              'title' => 'Province',
              'default' => TRUE,
              'alter_display' => 'alterStateProvinceID',
            ),
            'postal_code' => array(
              'title' => 'Postal Code',
              'default' => TRUE,
            ),
            'country_id' => array(
              'title' => 'Country',
              'default' => TRUE,
              'alter_display' => 'alterCountryID',
            ),
          ),
          'grouping' => 'contact-fields',
        ),
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
          ),
        'civicrm_phone' => array(
          'dao' => 'CRM_Core_DAO_Phone',
          'fields' => array(
            'phone' => array(
              'title' => ts('Phone'),
              'no_repeat' => TRUE,
              'default' => TRUE,
            ),
          ),
          'grouping' => 'contact-fields',
        ),
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
            'last_amount' => array(
              'title' => ts('Last Amount'),
              'required' => TRUE,
              'dbAlias' => "GROUP_CONCAT(contribution_civireport.total_amount ORDER BY contribution_civireport.receive_date SEPARATOR ', ')",
            ),
            'amounts_ytd' => array(
              'title' => ts('Amount - YTD 2015'),
              'required' => TRUE,
              'dbAlias' => "SUM(CASE WHEN year(contribution_civireport.receive_date)=2015 THEN contribution_civireport.total_amount ELSE 0 END)",
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
//          'received_DM' =>
//            array(
//              'title' => 'Received by DM',
//              'default' => TRUE,
////              'dbAlias' => "GROUP_CONCAT(value_tickets_8_civireport.feetype_itemnumber_24 ORDER BY contribution_civireport.receive_date SEPARATOR ', ')",
//              'dbAlias' => "GROUP_CONCAT(value_additional_contribution_info_29_civireport.donation_received_by_direct_mail_321 ORDER BY contribution_civireport.receive_date SEPARATOR ', ')",
//            ),
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
        ),
        'civicrm_contribution_ordinality' => array(
          'dao' => 'CRM_Contribute_DAO_Contribution',
          'alias' => 'cordinality',
          'filters' => array(
            'ordinality' => array(
              'title' => ts('Contribution Ordinality'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => array(
                0 => 'First by Contributor',
                1 => 'Second or Later by Contributor',
              ),
              'type' => CRM_Utils_Type::T_INT,
            ),
          ),
        ),
        'civicrm_contribution_recur' => array(
          'dao' => 'CRM_Contribute_DAO_ContributionRecur',
          'fields' => array(
            'id' => array(
              'required' => TRUE,
              'title' => ts("Series ID"),
            ),
            'start_date' => array(
              'required' => TRUE,
              'type' => CRM_Utils_Type::T_DATE,
              'title' => ts("Start Date - Recurring Series"),
            ),
            'contribution_status_id' => array(
              'required' => TRUE,
              'title' => ts('Recurring Series - Status'),
            ),
          ),
          'filters' => array(
            'start_date' => array(
              'title' => ts('Start Date - Recurring Series'),
              'type' => CRM_Report_Form::OP_DATE,
            ),
          ),
        ),
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
      $this->_from .= "
      LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
        ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND
          {$this->_aliases['civicrm_address']}.is_primary = 1 AND {$this->_aliases['civicrm_address']}.location_type_id != 3)";
//    $this->_from .= "
//      LEFT JOIN civicrm_value_tickets_8 {$this->_aliases['civicrm_value_tickets_8']}
//        ON {$this->_aliases['civicrm_contribution']}.id = {$this->_aliases['civicrm_value_tickets_8']}.entity_id";
//    $this->_from .= "
//        LEFT JOIN civicrm_value_additional_contribution_info_29 {$this->_aliases['civicrm_value_additional_contribution_info_29']}
//          ON {$this->_aliases['civicrm_contribution']}.id = {$this->_aliases['civicrm_value_additional_contribution_info_29']}.entity_id";
      $this->_from .= "
      LEFT JOIN (select contact_id, max(id) as id FROM civicrm_contribution_recur GROUP BY contact_id) recur_max_selector
        ON recur_max_selector.contact_id = {$this->_aliases['civicrm_contact']}.id";
      $this->_from .= "
      LEFT JOIN civicrm_contribution_recur {$this->_aliases['civicrm_contribution_recur']}
        ON {$this->_aliases['civicrm_contribution_recur']}.id = recur_max_selector.id";
      $this->_from .= "
      LEFT JOIN (select contact_id, max(id) as id FROM civicrm_membership GROUP BY contact_id) membership_max_selector
        ON membership_max_selector.contact_id = {$this->_aliases['civicrm_contact']}.id";
      $this->_from .= "
      LEFT JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
        ON {$this->_aliases['civicrm_membership']}.id = membership_max_selector.id";
      $this-> _from .= "
      LEFT  JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']}
                          ON {$this->_aliases['civicrm_membership_status']}.id =
                             {$this->_aliases['civicrm_membership']}.status_id ";
      $this->_from .= "
      LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
        ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
          {$this->_aliases['civicrm_phone']}.is_primary = 1)";

      if (!empty($this->_params['ordinality_value'])) {
        $this->_from .= "
              INNER JOIN (SELECT c.id, IF(COUNT(oc.id) = 0, 0, 1) AS ordinality FROM civicrm_contribution c LEFT JOIN civicrm_contribution oc ON c.contact_id = oc.contact_id AND oc.receive_date < c.receive_date GROUP BY c.id) {$this->_aliases['civicrm_contribution_ordinality']}
                      ON {$this->_aliases['civicrm_contribution_ordinality']}.id = {$this->_aliases['civicrm_contribution']}.id";
      }
    }

    function groupBy() {
      //$this->_groupBy = "GROUP BY " . $this->_aliases['civicrm_contribution_recur'] . ".id";
      //$this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id, {$this->_aliases['civicrm_contribution']}.id ";
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

        // handle address country and province id => value conversion
        if ($value = CRM_Utils_Array::value('civicrm_address_country_id', $row)) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
        if ($value = CRM_Utils_Array::value('civicrm_address_state_province_id', $row)) {
          $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
        }
        if ($value = CRM_Utils_Array::value('civicrm_contact_prefix_id', $row)) {
          $rows[$rowNum]['civicrm_contact_prefix_id'] = self::$prefixes[$value];
        }

        // handle contribution status id
        if ($value = CRM_Utils_Array::value('civicrm_contribution_recur_contribution_status_id', $row)) {
          $rows[$rowNum]['civicrm_contribution_recur_contribution_status_id'] = self::$contributionStatus[$value];
        }

        if (array_key_exists('civicrm_membership_membership_type_id', $row)) {
          if ($value = $row['civicrm_membership_membership_type_id']) {
            $rows[$rowNum]['civicrm_membership_membership_type_id'] = CRM_Member_PseudoConstant::membershipType($value, FALSE);
          }
          $entryFound = TRUE;
        }

        // Link to recurring series
        // e.g. http://lllc.local/civicrm/contact/view/contributionrecur?reset=1&id=13&cid=7481&context=contribution
        if (($value = CRM_Utils_Array::value('civicrm_contribution_recur_id', $row)) && CRM_Core_Permission::check('access CiviContribute')) {
          $url = CRM_Utils_System::url("civicrm/contact/view/contributionrecur",
            "reset=1&id=" . $row['civicrm_contribution_recur_id'] .
            "&cid=" . $row['civicrm_contact_id'] .
            "&action=view&context=contribution&selectedChild=contribute",
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_contribution_recur_id_link'] = $url;
          $rows[$rowNum]['civicrm_contribution_recur_id_hover'] = ts("View Details of this Recurring Series.");
          $entryFound = TRUE;
        }
      }
    }
  }

