<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
 * @copyright 	2010 by i-MSCP | http://i-mscp.net
 * @version 	SVN: $Id$
 * @link 		http://i-mscp.net
 * @author 		ispCP Team
 * @author 		i-MSCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 * Portions created by the i-MSCP Team are Copyright (C) 2010 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 */

require '../include/imscp-lib.php';

check_login(__FILE__);

/** @var $cfg iMSCP_Config_Handler_File */
$cfg = iMSCP_Registry::get('config');

$tpl = new iMSCP_pTemplate();

$tpl->define_dynamic('page', $cfg->ADMIN_TEMPLATE_PATH . '/reseller_add.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('hosting_plans', 'page');
$tpl->define_dynamic('rsl_ip_message', 'page');
$tpl->define_dynamic('rsl_ip_list', 'page');
$tpl->define_dynamic('rsl_ip_item', 'rsl_ip_list');

$tpl->assign(
	array(
		'TR_ADMIN_ADD_RESELLER_PAGE_TITLE' => tr('i-MSCP - Admin/Manage users/Add reseller'),
		'THEME_COLOR_PATH' => "../themes/{$cfg->USER_INITIAL_THEME}",
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => get_logo($_SESSION['user_id'])
	)
);

/**
 * Get Server IPs
 */
function get_server_ip($tpl, $sql) {

	$cfg = iMSCP_Registry::get('config');

	$query = "
		SELECT
			`ip_id`, `ip_number`, `ip_domain`
		FROM
			`server_ips`
		ORDER BY
			`ip_number`
	";

	$rs = exec_query($sql, $query);

	$i = 0;

	$reseller_ips = '';

	if ($rs->recordCount() == 0) {
		$tpl->assign(
			array(
				'RSL_IP_MESSAGE' => tr('Reseller IP list is empty!'),
				'RSL_IP_LIST' => ''
			)
		);

		$tpl->parse('RSL_IP_MESSAGE', 'rsl_ip_message');
	} else {
		$tpl->assign(
			array(
				'TR_RSL_IP_NUMBER' => tr('No.'),
				'TR_RSL_IP_ASSIGN' => tr('Assign'),
				'TR_RSL_IP_LABEL' => tr('Label'),
				'TR_RSL_IP_IP' => tr('Number'),
			)
		);
		while (!$rs->EOF) {
			$tpl->assign(
				array(
					'RSL_IP_CLASS' => ($i % 2 == 0) ? 'content' : 'content2',
				)
			);

			$ip_id = $rs->fields['ip_id'];

			$ip_var_name = "ip_$ip_id";

			if (isset($_POST[$ip_var_name]) && $_POST[$ip_var_name] == 'asgned') {
				$ip_item_assigned = $cfg->HTML_CHECKED;

				$reseller_ips .= "$ip_id;";
			} else {
				$ip_item_assigned = '';
			}

			$tpl->assign(
				array(
					'RSL_IP_NUMBER' => $i + 1,
					'RSL_IP_LABEL' => $rs->fields['ip_domain'],
					'RSL_IP_IP' => $rs->fields['ip_number'],
					'RSL_IP_CKB_NAME' => $ip_var_name,
					'RSL_IP_CKB_VALUE' => 'asgned',
					'RSL_IP_ITEM_ASSIGNED' => $ip_item_assigned,
				)
			);

			$tpl->parse('RSL_IP_ITEM', '.rsl_ip_item');

			$rs->moveNext();

			$i++;
		}

		$tpl->parse('RSL_IP_LIST', 'rsl_ip_list');

		$tpl->assign('RSL_IP_MESSAGE', '');
	}

	return $reseller_ips;
}

/**
 * @param  $tpl
 * @param  $sql
 * @return void
 */
function add_reseller($tpl, $sql) {

	global $reseller_ips;
	$cfg = iMSCP_Registry::get('config');

	if (isset($_POST['uaction']) && $_POST['uaction'] === 'add_reseller') {
		if (check_user_data()) {
			$upass = crypt_user_pass($_POST['pass']);

			$user_id = $_SESSION['user_id'];

			$username = clean_input($_POST['username']);
			$fname = clean_input($_POST['fname']);
			$lname = clean_input($_POST['lname']);
			$gender = clean_input($_POST['gender']);
			$firm = clean_input($_POST['firm']);
			$zip = clean_input($_POST['zip']);
			$city = clean_input($_POST['city']);
			$state = clean_input($_POST['state']);
			$country = clean_input($_POST['country']);
			$email = clean_input($_POST['email']);
			$phone = clean_input($_POST['phone']);
			$fax = clean_input($_POST['fax']);
			$street1 = clean_input($_POST['street1']);
			$street2 = clean_input($_POST['street2']);

			$query = "
				INSERT INTO `admin` (
					`admin_name`,
					`admin_pass`,
					`admin_type`,
					`domain_created`,
					`created_by`,
					`fname`,
					`lname`,
					`firm`,
					`zip`,
					`city`,
					`state`,
					`country`,
					`email`,
					`phone`,
					`fax`,
					`street1`,
					`street2`,
					`gender`
				) VALUES (
					?,
					?,
					'reseller',
					unix_timestamp(),
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?
				)
			";

			exec_query($sql, $query, array($username,
					$upass,
					$user_id,
					$fname,
					$lname,
					$firm,
					$zip,
					$city,
					$state,
					$country,
					$email,
					$phone,
					$fax,
					$street1,
					$street2,
					$gender)
			);

			$new_admin_id = $sql->insertId();

			$user_logged = $_SESSION['user_logged'];

			write_log("$user_logged: add reseller: $username");

			$user_def_lang = $_SESSION['user_def_lang'];
			$user_theme_color = $_SESSION['user_theme'];
			$user_logo = 0;

			$query = "
				INSERT INTO `user_gui_props`
					(
					`user_id`,
					`lang`,
					`layout`,
					`logo`
					)
				VALUES
					(?, ?, ?, ?)
			";

			exec_query($sql, $query, array($new_admin_id,
					$user_def_lang,
					$user_theme_color,
					$user_logo)
			);

			/*
			 * 'reseller_props' table entry;
			 */

			$nreseller_max_domain_cnt = clean_input($_POST['nreseller_max_domain_cnt']);
			$nreseller_max_subdomain_cnt = clean_input($_POST['nreseller_max_subdomain_cnt']);
			$nreseller_max_alias_cnt = clean_input($_POST['nreseller_max_alias_cnt']);
			$nreseller_max_mail_cnt = clean_input($_POST['nreseller_max_mail_cnt']);
			$nreseller_max_ftp_cnt = clean_input($_POST['nreseller_max_ftp_cnt']);
			$nreseller_max_sql_db_cnt = clean_input($_POST['nreseller_max_sql_db_cnt']);
			$nreseller_max_sql_user_cnt = clean_input($_POST['nreseller_max_sql_user_cnt']);
			$nreseller_max_traffic = clean_input($_POST['nreseller_max_traffic']);
			$nreseller_max_disk = clean_input($_POST['nreseller_max_disk']);
			$nreseller_software_allowed = clean_input($_POST['nreseller_software_allowed']);
			$nreseller_softwaredepot_allowed = clean_input($_POST['nreseller_softwaredepot_allowed']);
            $nreseller_websoftwaredepot_allowed = clean_input($_POST['nreseller_websoftwaredepot_allowed']);
			$customer_id = clean_input($_POST['customer_id']);
			$support_system = clean_input($_POST['support_system']);

			$query = "
				INSERT INTO `reseller_props` (
					`reseller_id`, `reseller_ips`,
					`max_dmn_cnt`, `current_dmn_cnt`,
					`max_sub_cnt`, `current_sub_cnt`,
					`max_als_cnt`, `current_als_cnt`,
					`max_mail_cnt`, `current_mail_cnt`,
					`max_ftp_cnt`, `current_ftp_cnt`,
					`max_sql_db_cnt`, `current_sql_db_cnt`,
					`max_sql_user_cnt`, `current_sql_user_cnt`,
					`max_traff_amnt`, `current_traff_amnt`,
					`max_disk_amnt`, `current_disk_amnt`,
					`support_system`, `customer_id`,
					`software_allowed`, `softwaredepot_allowed`,
					`websoftwaredepot_allowed`
				) VALUES (
					?, ?,
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, '0',
					?, ?,
					?, ?,
					?
				)
				";

			exec_query($sql, $query, array($new_admin_id, $reseller_ips,
					$nreseller_max_domain_cnt,
					$nreseller_max_subdomain_cnt,
					$nreseller_max_alias_cnt,
					$nreseller_max_mail_cnt,
					$nreseller_max_ftp_cnt,
					$nreseller_max_sql_db_cnt,
					$nreseller_max_sql_user_cnt,
					$nreseller_max_traffic,
					$nreseller_max_disk,
					$support_system,
					$customer_id,
					$nreseller_software_allowed,
					$nreseller_softwaredepot_allowed,
                    $nreseller_websoftwaredepot_allowed)
			);

			send_add_user_auto_msg($user_id,
				clean_input($_POST['username']),
				$_POST['pass'],
				clean_input($_POST['email']),
				clean_input($_POST['fname']),
				clean_input($_POST['lname']),
				tr('Reseller'),
				$gender
			);
			
			@mkdir($cfg->GUI_SOFTWARE_DIR."/".$new_admin_id,0755,true);
			#@chown($cfg->GUI_SOFTWARE_DIR."/".$new_admin_id, "vu2000");
			#@chgrp($cfg->GUI_SOFTWARE_DIR."/".$new_admin_id, "www-data");

			$_SESSION['reseller_added'] = 1;

			user_goto('manage_users.php');
		} else {
			$tpl->assign(
				array(
					'EMAIL' => clean_input($_POST['email'], true),
					'USERNAME' => clean_input($_POST['username'], true),
					'FIRST_NAME' => clean_input($_POST['fname'], true),
					'CUSTOMER_ID' => clean_input($_POST['customer_id'], true),
					'LAST_NAME' => clean_input($_POST['lname'], true),
					'FIRM' => clean_input($_POST['firm'], true),
					'ZIP' => clean_input($_POST['zip'], true),
					'CITY' => clean_input($_POST['city'], true),
					'STATE' => clean_input($_POST['state'], true),
					'COUNTRY' => clean_input($_POST['country'], true),
					'STREET_1' => clean_input($_POST['street1'], true),
					'STREET_2' => clean_input($_POST['street2'], true),
					'PHONE' => clean_input($_POST['phone'], true),
					'FAX' => clean_input($_POST['fax'], true),
					'VL_MALE' => (($_POST['gender'] == 'M') ? $cfg->HTML_SELECTED : ''),
					'VL_FEMALE' => (($_POST['gender'] == 'F') ? $cfg->HTML_SELECTED : ''),
					'VL_UNKNOWN' => ((($_POST['gender'] == 'U') || (empty($_POST['gender']))) ? $cfg->HTML_SELECTED : ''),

					'MAX_DOMAIN_COUNT' => clean_input($_POST['nreseller_max_domain_cnt'], true),
					'MAX_SUBDOMAIN_COUNT' => clean_input($_POST['nreseller_max_subdomain_cnt'], true),
					'MAX_ALIASES_COUNT' => clean_input($_POST['nreseller_max_alias_cnt'], true),
					'MAX_MAIL_USERS_COUNT' => clean_input($_POST['nreseller_max_mail_cnt'], true),
					'MAX_FTP_USERS_COUNT' => clean_input($_POST['nreseller_max_ftp_cnt'], true),
					'MAX_SQLDB_COUNT' => clean_input($_POST['nreseller_max_sql_db_cnt'], true),
					'MAX_SQL_USERS_COUNT' => clean_input($_POST['nreseller_max_sql_user_cnt'], true),
					'MAX_TRAFFIC_AMOUNT' => clean_input($_POST['nreseller_max_traffic'], true),
					'SUPPORT_SYSTEM' => clean_input($_POST['support_system'], true),
					'MAX_DISK_AMOUNT' => clean_input($_POST['nreseller_max_disk'], true),
					'SOFTWARE_ALLOWED' => clean_input($_POST['nreseller_software_allowed'],true),
					'SOFTWAREDEPOT_ALLOWED' => clean_input($_POST['nreseller_softwaredepot_allowed'],true),
                    'WEBSOFTWAREDEPOT_ALLOWED' => clean_input($_POST['nreseller_websoftwaredepot_allowed'],true),
					'VL_SOFTWAREY' => (($_POST['nreseller_software_allowed'] == 'yes') ? $cfg->HTML_CHECKED : ''),
					'VL_SOFTWAREN' => (($_POST['nreseller_software_allowed'] != 'yes') ? $cfg->HTML_CHECKED : ''),
					'VL_SOFTWAREDEPOTY' => (($_POST['nreseller_softwaredepot_allowed'] == 'yes') ? $cfg->HTML_CHECKED : ''),
					'VL_SOFTWAREDEPOTN' => (($_POST['nreseller_softwaredepot_allowed'] != 'yes') ? $cfg->HTML_CHECKED : ''),
                    'VL_WEBSOFTWAREDEPOTY' => (($_POST['nreseller_websoftwaredepot_allowed'] == 'yes') ? $cfg->HTML_CHECKED : ''),
					'VL_WEBSOFTWAREDEPOTN' => (($_POST['nreseller_websoftwaredepot_allowed'] != 'yes') ? $cfg->HTML_CHECKED : '')
				)
			);
		}
	} // not add
	else {
		$tpl->assign(
			array(
				'EMAIL' => '',
				'USERNAME' => '',

				'FIRST_NAME' => '',
				'CUSTOMER_ID' => '',
				'LAST_NAME' => '',
				'FIRM' => '',
				'ZIP' => '',
				'CITY' => '',
				'STATE' => '',
				'COUNTRY' => '',
				'STREET_1' => '',
				'STREET_2' => '',
				'PHONE' => '',
				'FAX' => '',
				'VL_MALE' => '',
				'VL_FEMALE' => '',
				'VL_UNKNOWN' => $cfg->HTML_SELECTED,

				'MAX_DOMAIN_COUNT' => '',
				'MAX_SUBDOMAIN_COUNT' => '',
				'MAX_ALIASES_COUNT' => '',
				'MAX_MAIL_USERS_COUNT' => '',
				'MAX_FTP_USERS_COUNT' => '',
				'MAX_SQLDB_COUNT' => '',
				'MAX_SQL_USERS_COUNT' => '',
				'MAX_TRAFFIC_AMOUNT' => '',
				'MAX_DISK_AMOUNT' => '',
				'SOFTWARE_ALLOWED' => '',
				'SOFTWAREDEPOT_ALLOWED' => '',
				'VL_SOFTWAREN' => $cfg->HTML_CHECKED,
				'VL_SOFTWAREDEPOTY' => $cfg->HTML_CHECKED,
                'VL_WEBSOFTWAREDEPOTY' => $cfg->HTML_CHECKED
			)
		);
	}
}

function check_user_data() {

	global $reseller_ips;

	$cfg = iMSCP_Registry::get('config');
	$sql = iMSCP_Registry::get('db');

	$username = clean_input($_POST['username']);

	$query = "
		SELECT
			`admin_id`
		FROM
			`admin`
		WHERE
			`admin_name` = ?
	";

	$rs = exec_query($sql, $query, $username);

	if ($rs->recordCount() != 0) {
		set_page_message(tr('This user name already exist!'), 'warning');

		return false;
	}
	if (!validates_username(clean_input($_POST['username']))) {
		set_page_message(tr("Incorrect username length or syntax!"), 'error');

		return false;
	}
	if (!chk_password($_POST['pass'])) {
		if ($cfg->PASSWD_STRONG) {
			set_page_message(
				sprintf(
					tr('The password must be at least %s long and contain letters and numbers to be valid.'),
					$cfg->PASSWD_CHARS
				),
				'error'
			);
		} else {
			set_page_message(
				sprintf(
					tr('Password data is shorter than %s signs or includes not permitted signs!'),
					$cfg->PASSWD_CHARS
				), 'error'
			);
		}

		return false;
	}
	if ($_POST['pass'] != $_POST['pass_rep']) {
		set_page_message(tr("Entered passwords do not match!"), 'error');

		return false;
	}
	if (!chk_email(clean_input($_POST['email']))) {
		set_page_message(tr("Incorrect email syntax!"), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_domain_cnt'], null)) {
		set_page_message(tr("Incorrect domains limit!"), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_subdomain_cnt'], -1)) {
		set_page_message(tr("Incorrect subdomains limit!"), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_alias_cnt'], -1)) {
		set_page_message(tr('Incorrect aliases limit!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_ftp_cnt'], -1)) {
		set_page_message(tr('Incorrect FTP accounts limit!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_mail_cnt'], -1)) {
		set_page_message(tr('Incorrect mail accounts limit!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_sql_db_cnt'], -1)) {
		set_page_message(tr('Incorrect SQL databases limit!'), 'error');

		return false;
	} else if ($_POST['nreseller_max_sql_db_cnt'] == -1 && $_POST['nreseller_max_sql_user_cnt'] != -1) {
		set_page_message(tr('SQL databases limit is <i>disabled</i> but SQL users limit not!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_sql_user_cnt'], -1)) {
		set_page_message(tr('Incorrect SQL users limit!'), 'error');

		return false;
	} else if ($_POST['nreseller_max_sql_db_cnt'] != -1 && $_POST['nreseller_max_sql_user_cnt'] == -1) {
		set_page_message(tr('SQL users limit is <i>disabled</i> but SQL databases limit not!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_traffic'], null)) {
		set_page_message(tr('Incorrect traffic limit!'), 'error');

		return false;
	}
	if (!imscp_limit_check($_POST['nreseller_max_disk'], null)) {
		set_page_message(tr('Incorrect disk quota limit!'), 'error');

		return false;
	}
	if ($reseller_ips == '') {
		set_page_message(tr('You must assign at least one IP number for a reseller!'), 'error');

		return false;
	}

	return true;
}

/*
 *
 * static page messages.
 *
 */
gen_admin_mainmenu($tpl, $cfg->ADMIN_TEMPLATE_PATH . '/main_menu_users_manage.tpl');
gen_admin_menu($tpl, $cfg->ADMIN_TEMPLATE_PATH . '/menu_users_manage.tpl');

$reseller_ips = get_server_ip($tpl, $sql);

add_reseller($tpl, $sql);

$tpl->assign(
	array(
		'TR_ADD_RESELLER' => tr('Add reseller'),
		'TR_CORE_DATA' => tr('Core data'),
		'TR_USERNAME' => tr('Username'),
		'TR_PASSWORD' => tr('Password'),
		'TR_PASSWORD_REPEAT' => tr('Repeat password'),
		'TR_EMAIL' => tr('Email'),
		'TR_MAX_DOMAIN_COUNT' => tr('Domains limit<br><i>(0 unlimited)</i>'),
		'TR_MAX_SUBDOMAIN_COUNT' => tr('Subdomains limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_ALIASES_COUNT' => tr('Aliases limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_MAIL_USERS_COUNT' => tr('Mail accounts limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_FTP_USERS_COUNT' => tr('FTP accounts limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_SQLDB_COUNT' => tr('SQL databases limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_SQL_USERS_COUNT' => tr('SQL users limit<br><i>(-1 disabled, 0 unlimited)</i>'),
		'TR_MAX_TRAFFIC_AMOUNT' => tr('Traffic limit [MB]<br><i>(0 unlimited)</i>'),
		'TR_MAX_DISK_AMOUNT' => tr('Disk limit [MB]<br><i>(0 unlimited)</i>'),
		'TR_PHP' => tr('PHP'),
		'TR_PERL_CGI' => tr('CGI / Perl'),
		'TR_JSP' => tr('JSP'),
		'TR_SSI' => tr('SSI'),
		'TR_FRONTPAGE_EXT' => tr('Frontpage extensions'),
		'TR_BACKUP_RESTORE' => tr('Backup and restore'),
		'TR_CUSTOM_ERROR_PAGES' => tr('Custom error pages'),
		'TR_PROTECTED_AREAS' => tr('Protected areas'),
		'TR_WEBMAIL' => tr('Webmail'),
		'TR_DIR_LIST' => tr('Directory listing'),
		'TR_APACHE_LOGFILES' => tr('Apache logfiles'),
		'TR_AWSTATS' => tr('AwStats'),
		'TR_LOGO_UPLOAD' => tr('Logo upload'),
		'TR_YES' => tr('yes'),
		'TR_NO' => tr('no'),
		'TR_SUPPORT_SYSTEM' => tr('Support system'),
		'TR_SOFTWARE_ALLOWED' => tr('i-MSCP application installer'),
		'TR_SOFTWAREDEPOT_ALLOWED' => tr('Can use software depot'),
        'TR_WEBSOFTWAREDEPOT_ALLOWED' => tr('Can use websoftware depot'),

		'TR_RESELLER_IPS' => tr('Reseller IPs'),

		'TR_ADDITIONAL_DATA' => tr('Additional data'),
		'TR_CUSTOMER_ID' => tr('Customer ID'),
		'TR_FIRST_NAME' => tr('First name'),
		'TR_LAST_NAME' => tr('Last name'),
		'TR_LAST_NAME' => tr('Last name'),
		'TR_GENDER' => tr('Gender'),
		'TR_MALE' => tr('Male'),
		'TR_FEMALE' => tr('Female'),
		'TR_UNKNOWN' => tr('Unknown'),
		'TR_COMPANY' => tr('Company'),
		'TR_ZIP_POSTAL_CODE' => tr('Zip/Postal code'),
		'TR_CITY' => tr('City'),
		'TR_STATE' => tr('State/Province'),
		'TR_COUNTRY' => tr('Country'),
		'TR_STREET_1' => tr('Street 1'),
		'TR_STREET_2' => tr('Street 2'),
		'TR_PHONE' => tr('Phone'),
		'TR_FAX' => tr('Fax'),
		'TR_PHONE' => tr('Phone'),
		'TR_ADD' => tr('Add'),
		'GENPAS' => passgen()
	)
);

generatePageMessage($tpl);

$tpl->parse('PAGE', 'page');
$tpl->prnt();

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug();
}

unsetMessages();