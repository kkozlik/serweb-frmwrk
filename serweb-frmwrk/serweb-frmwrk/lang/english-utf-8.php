<?php
/**
 * $Id: english-utf-8.php,v 1.38 2006/05/23 09:13:38 kozlik Exp $
 *
 *
 * Prefixes:
 * 'fe' - form error
 * 'ff' - form field
 * 'msg_*_s' - message short
 * 'msg_*_l' - message long
 * 'l' - link
 * 'th' - table heading
 * 'err' - error
 */

$lang_set['charset'] = 			"utf-8";
$lang_set['date_time_format'] = "Y-m-d H:i";
$lang_set['date_format'] = 		"Y-m-d";
$lang_set['time_format'] = 		"H:i";


$lang_str['msg_changes_saved_s'] = 				"Changes saved";
$lang_str['msg_changes_saved_l'] = 				"Your changes have been saved";
$lang_str['fe_not_filled_item_label'] = 		"you must fill item label";
$lang_str['fe_not_filled_item_value'] = 		"you must fill item value";

$lang_str['b_extended_settings'] =		 		"Extended settings";
$lang_str['b_submit'] =		 					"Save";
$lang_str['b_apply'] =		 					"Apply";
$lang_str['b_cancel'] =		 					"Cancel";
$lang_str['b_search'] = 						"Search";
$lang_str['b_previous'] = 						"Previous";
$lang_str['b_next'] = 							"Next";
$lang_str['b_finish'] = 						"Finish";
$lang_str['b_advanced'] = 						"Advanced";
$lang_str['b_add'] = 							"Add";
$lang_str['b_insert'] = 						"Insert";
$lang_str['b_select'] = 						"Select";
$lang_str['b_ok'] = 							"OK";
$lang_str['b_reset'] = 							"Reset";

$lang_str['not_exists'] = 						"does not exists";

$lang_str['confirm_ser_restart'] =              "The applications will be automatically restarted for the change to take effect. \nThis may cause short service outage.";

/* ------------------------------------------------------------*/
/*      error messages                                         */
/* ------------------------------------------------------------*/

$lang_str['fe_not_valid_email'] =	 			"not valid email address";
$lang_str['fe_is_not_valid_email'] =	 		"is not valid email address";
$lang_str['fe_not_valid_sip'] = 				"not valid SIP URI";
$lang_str['fe_not_valid_phonenumber'] = 		"not valid phonenumber";
$lang_str['fe_not_filled_sip'] = 				"you must fill sip address";
$lang_str['fe_passwords_not_match'] =			"passwords not match";
$lang_str['fe_not_filled_username'] = 			"You must fill username";
$lang_str['fe_not_allowed_uri'] = 				"Not allowed sip address";
$lang_str['fe_max_entries_reached'] = 			"Maximum number of entries reached";
$lang_str['fe_not_valid_username'] = 			"not valid username";
$lang_str['fe_subs_class_already_exists'] = 	"This subscriber class already exists";
$lang_str['domain_not_found'] = 				"Domain of user not found";
$lang_str['ERR_ONT_001'] =	 					"[Error Code 001] - Missing Field Value";
$lang_str['ERR_ONT_002'] =	 					"[Error Code 002] - Invalid Syntax";
$lang_str['ERR_ONT_003'] =	 					"[Error Code 003] - Field value must be unique";
$lang_str['ERR_ONT_020'] =                      "[Error Code 020] - Field value does not exist for";
$lang_str['ERR_OPR_FAILED_NO_FIELD'] =          "[Error Code 070] - Operation Failed: The selection for field name no longer exists:";
$lang_str['ERR_OPR_FAILED_NO_ENTRY'] =          "[Error Code 071] - Operation Failed: The entry no longer exists";
$lang_str['ERR_INV_VAL'] =                      "[Error Code 082] - Invalid value: ";
$lang_str['ERR_INV_RANGE'] =                    "[Error Code 7024] - Invalid input value. Valid range is <min> - <max>.";
$lang_str['ERR_INV_FQDN'] =                     "[Error Code 7025] - Invalid FQDN name";
$lang_str['ERR_MIN_LT_MAX'] =                   "[Error Code 7100] - The value entered in field <param_min> must be less than the value provided in field <param_max>";
$lang_str['err_opt_inconsistent_values'] =      "[Error Code 8265] - Inconsistent values for <var1> - <var2> - <var3>";

$lang_str['err_max_entries'] =                  "[Error Code 8300] - Maximum number of <name> has already been created.";
$lang_str['err_dup_assign'] =                   "[Error Code 8301] - The <object1> is already assigned to the <object2>.";
$lang_str['err_in_use'] =                       "[Error Code 8302] - The <object1> can not be deleted as long as it is used by the <object2>.";
$lang_str['err_empty_value'] =                  "[Error Code 8303] - A value for the <name> must be entered.";
$lang_str['err_dup_entry'] =                    "[Error Code 8304] - The <object> with the same <name> already exists.";
$lang_str['err_missing_entry'] =                "[Error Code 8305] - The <object> no longer exists in the system.";
$lang_str['err_value_not_selected'] =           "[Error Code 8306] - The <name> must be selected.";
$lang_str['err_greater_than'] =                 "[Error Code 8307] - The <var1> must be greater than the <var2>.";
$lang_str['err_desc_order'] =                   "[Error Code 8308] - The input values of the <var1>, <var2>, <var3> shall be in a strictly descending order.";
$lang_str['err_desc_order_list'] =              "[Error Code 8308] - The input values of the <var_list> shall be in a strictly descending order.";
$lang_str['err_out_of_range'] =                 "[Error Code 8309] - The input value of the <name> is outside the valid range <min> - <max>.";
$lang_str['err_invalid_regexp'] =               "[Error Code 8310] - The input value \"#VALUE#\" for the <name> is not a valid regular expression.";
$lang_str['err_invalid_number'] =               "[Error Code 8311] - The input value \"#VALUE#\" for the <name> is not a valid number.";
$lang_str['err_invalid_number_prefix'] =        "[Error Code 8312] - The input value \"#VALUE#\" for the <name> is not a valid number prefix.";
$lang_str['err_invalid_ip_addr'] =              "[Error Code 8313] - The input value \"#VALUE#\" for the <name> is not a valid IPv4 address.";
$lang_str['err_invalid_sip_uri'] =              "[Error Code 8314] - The input value \"#VALUE#\" for the <name> is not a valid SIP URI.";
$lang_str['err_invalid_ip_addr_port'] =         "[Error Code 8316] - The input value \"#VALUE#\" for the <name> is not a valid {IPv4 address}:{port number}.";
$lang_str['err_invalid_ip_addr_fqdn'] =         "[Error Code 8317] - The input value \"#VALUE#\" for the <name> is neither a valid IPv4 address nor a valid fully qualified domain name.";
$lang_str['err_invalid_ip_addr_netmask'] =      "[Error Code 8319] - The input value \"#VALUE#\" for the <name> is not a valid {IP address}/{netmask}.";
$lang_str['err_invalid_phone_number'] =         "[Error Code 8320] - The input value \"#VALUE#\" for the <name> is not a valid telephone number.";
$lang_str['err_invalid_service_name'] =         "[Error Code 8321] - The input value \"#VALUE#\" for the <name> is not a valid service name.";
$lang_str['err_invalid_obj_name'] =             "[Error Code 8330] - The input value \"#VALUE#\" for the <name> is not a valid object name.";
$lang_str['err_invalid_fqdn'] =                 "[Error Code 8333] - The input value \"#VALUE#\" for the <name> is not a valid fully qualified domain name.";
$lang_str['err_invalid_fqdn_ip_ipport'] =       "[Error Code 8337] - The input value \"#VALUE#\" for the <name> is neither a valid IPv4 address nor a valid <IP address>:<port number> nor a valid fully qualified domain name.";


/* ------------------------------------------------------------*/
/*      Options                                                */
/* ------------------------------------------------------------*/

$lang_str['warn_option_mode_change'] =          "There are some changes in the option values. Do you want to continue? If so, your changes will be lost.";

/* ------------------------------------------------------------*/
/*      P-CSCF Options                                         */
/* ------------------------------------------------------------*/

$lang_str['err_icscf_route_service_dne']=       "[Error Code 5083] - Insert failed: Route Service indicated for I-CSCF Route does not exist";


/* ------------------------------------------------------------*/
/*      S-CSCF Options                                         */
/* ------------------------------------------------------------*/

$lang_str['err_opt_DefaultExpires'] = 			"[Error Code 6070] - Invalid value entered. Default expiration interval must be in the range 300 - 172800.";
$lang_str['err_opt_MaxExpires'] = 				"[Error Code 6071] - Invalid value entered. Maximum Expiration Interval must be in the range 3600 - 172800.";
$lang_str['err_opt_MinExpires'] = 				"[Error Code 6072] - Invalid value entered. Minimum Expiration Interval must be in the range 60 - 3600.";
$lang_str['err_opt_MaxMinExpires'] = 			"[Error Code 6073] - Invalid value entered. Maximum Expiration Interval must be greater than the Minimum Expiration Interval value.";
$lang_str['err_opt_UeRegAudtPrd'] = 			"[Error Code 6074] - Invalid value entered. Registration Audit Period must be in the range 1 - 1440.";
$lang_str['err_opt_DefMinExpires'] = 			"[Error Code 6101] - Invalid value entered. Default Expiration Interval must be greater than the Minimum Expiration Interval value.";
$lang_str['err_opt_DefMaxExpires'] = 			"[Error Code 6102] - Invalid value entered. Default Expiration Interval must be lesser than the Maximum Expiration Interval value.";
$lang_str['err_opt_7021_empty_value'] =         "[Error Code 7021] - A value must be entered";
$lang_str['err_opt_7022_not_a_number'] =        "[Error Code 7022] - Not a valid number";
$lang_str['err_opt_7024_out_of_range'] =        "[Error Code 7024] - Input value is outside the valid range <min> - <max>";

/* ------------------------------------------------------------*/
/*      TCP Options                                            */
/* ------------------------------------------------------------*/

$lang_str['err_opt_tcp_wq_max_lt_con_wq_max'] = "[Error Code 8250] - \"Per-socket maximum write queue\" must be less than \"Per-system maximum write queue\"";
$lang_str['err_opt_tcp_lifetime_lt_timeout'] =  "[Error Code 8251] - The \"TCP Connection Lifetine\" must be larger than the \"TCP Connect Timeout\"";
$lang_str['err_opt_tcp_alarm_order'] =          "[Error Code 8252] - The values of \"TCP Connection Usage Critical Alarm Assert\", \"TCP Connection Usage Critical Alarm Clear\", \"TCP Connection Usage Major Alarm Assert\", \"TCP Connection Usage Major Alarm Clear\", \"TCP Connection Usage Minor Alarm Assert\", \"TCP Connection Usage Minor Alarm Clear\" must be in a strictly descending order";

/* ------------------------------------------------------------*/
/*      TCP Connections                                        */
/* ------------------------------------------------------------*/

$lang_str['err_maint_tcp_conn_server_ns'] =     "[Error Code 7030] - A server must be selected";
$lang_str['err_maint_command_failed'] =         "[Error Code 7026] - Command failed";

$lang_str['maint_tcp_connections_rpt_head'] =   "TCP Connections";

/* ------------------------------------------------------------*/
/*      SCTP Associations                                      */
/* ------------------------------------------------------------*/

$lang_str['err_maint_sctp_conn_server_ns'] =    $lang_str['err_maint_tcp_conn_server_ns'];

$lang_str['maint_sctp_associtaions_rpt_head'] = "SCTP Associations";

/* ------------------------------------------------------------*/
/*      VCC routing                                            */
/* ------------------------------------------------------------*/


$lang_str['fe_missing_field_value'] = 			"[Error Code 001]  - Missing field value.";
$lang_str['fe_vcc_table_is_full'] = 			"[Error Code 5100] - Insert failed: The maximum allowed VCC routes have already been entered.";
$lang_str['fe_vcc_already_exists'] = 			"[Error Code 5101] - Insert failed: The specified VCC route prefix already exists.";
$lang_str['fe_not_valid_vcc_prefix'] = 			"[Error Code 5102] - Insert failed: The VCC route prefix contains an illegal character.";
$lang_str['fe_not_exists_vcc_contact_i'] =		"[Error Code 5103] - Insert failed: The specified route name does not exist.";
$lang_str['fe_not_exists_vcc_contact_e'] =		"[Error Code 5104] - Edit failed: The specified route name does not exist.";

$lang_str['vcc_confirm_change'] = 				"Changing a VCC route that is in use could impact signaling using the route. Do you wish to continue?";
$lang_str['vcc_confirm_override'] = 			"The specified VCC route will serve digits formerly served by route <route_prefix> and route service <contact>. Do you wish to continue?";
$lang_str['vcc_confirm_superset'] = 			"The specified VCC route is a substring of longer VCC route prefixes beginning with the same digits. Do you wish to continue?";
$lang_str['vcc_confirm_no_route']= 			    "The specified VCC route change will leave no VCC route to route service <contact>. Do you wish to continue?";

$lang_str['msg_vcc_added_s'] = 					"VCC Route created";
$lang_str['msg_vcc_added_l'] = 					"New VCC route has been created";

$lang_str['msg_vcc_deleted_s'] = 				"VCC Route deleted";
$lang_str['msg_vcc_deleted_l'] = 				"VCC route has been deleted";

/* ------------------------------------------------------------*/
/*      Subscriber Constraints                                 */
/* ------------------------------------------------------------*/


$lang_str['err_ifc_privID_subs_range'] = 		"[Error Code 6033] - Invalid value entered. Max. Private IDs per subscription must be in the range 100-45000.";

$lang_str['err_ifc_privID_subs_decreased'] = 	"[Error Code 6075] - Invalid value entered. Max. Private IDs per subscription can not be decreased.";
$lang_str['err_ifc_pubID_privID_decreased'] = 	"[Error Code 6076] - Invalid value entered. Max. Public IDs per private ID can not be decreased.";
$lang_str['err_ifc_IG_privID_decreased'] = 		"[Error Code 6077] - Invalid value entered. Implicit Sets per private ID can not be decreased.";
$lang_str['err_ifc_pubID_IG_decreased'] = 		"[Error Code 6078] - Invalid value entered. Max. Public IDs per Implicit Set can not be decreased.";

$lang_str['subscriber_constraints_warning'] = 	"Warning: Changes to these fields values will become permanent. The field values will not be allowed to be lowered once changed.\n\nAre you sure your want to apply these changes?";

/* ------------------------------------------------------------*/
/*      Subscriber classes                                     */
/* ------------------------------------------------------------*/

$lang_str['fe_not_valid_class_name'] =			"[Error Code 6013] - Invalid value entered. Subscriber Class is not valid. A Subscriber Class Name must be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum of 32 characters";
$lang_str['fe_subs_class_not_valid_desc'] =		"[Error Code 6083] - Invalid value entered. Description is not valid. A Subscriber Class Name must be any string containing any printable character and a maximum of 255 characters";
$lang_str['fe_subs_class_used_in_service'] =	"[Error Code 6014] - Operation Failed. Services assigned to this class.";
$lang_str['fe_subs_class_used_by_user'] =		"[Error Code 6032] - Operation Failed. Subscribers are associated with this Subscriber Class.";
$lang_str['fe_subs_class_not_editable'] = 		"This subscriber class can not be changed";
$lang_str['fe_subs_class_not_deleteable'] = 	"This subscriber class can not be deleted";
$lang_str['fe_subs_class_to_edit_not_exists'] =	"[Error Code 6084] - Operating failed. Subscriber class you selected does not exists no more. May be someone deleted it concurrently.";
$lang_str['capacity_subs_class_reached'] = 		"[Error Code 6085] - Operating failed. Maximum number of subscriber classes have already been created.";

/* ------------------------------------------------------------*/
/*      POP                                                    */
/* ------------------------------------------------------------*/


$lang_str['fe_pop_already_exists'] =            "[Error Code 6087] - Invalid value entered. POP with this ID already exists";

$lang_str['fe_not_valid_uri_for_pop'] =         "[Error Code 6035] - Invalid value entered. POP: <pop_name> URI is not valid. Please verify that the POP Name URI  is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document.";
$lang_str['fe_not_valid_uri_for_route'] =       "[Error Code 6016] - Invalid value entered. Route Service: <route_id> URI is not valid. Please verify that the Route Service URI  is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['fe_sips_not_supported'] =            "[Error Code 6055] - Invalid value entered. SIPS is not supported.";

$lang_str['pop_changed_but_no_commited'] =      "POP Routing changes have been made and need to be committed.  Click the 'Commit' link to activate the changes.";

$lang_str['pop'] =                              "POP";

$lang_str['pop_name'] =                         "POP Name";
$lang_str['pop_func_iscscf'] =                  "I/S-CSCF Functions";
$lang_str['pop_func_ssr'] =                     "SSR-RM Functions";
$lang_str['pop_reg_type'] =                     "Registration Type";
$lang_str['pop_hss_mode'] =                     "HSS Mode";

$lang_str['msg_pop_created_s'] =                "POP created";
$lang_str['msg_pop_created_l'] =                "New POP has been created";
$lang_str['msg_pop_deleted_s'] =                "POP deleted";
$lang_str['msg_pop_deleted_l'] =                "POP has been deleted";


$lang_str['err_max_pop_per_ne'] =               "[Error Code 121] - Insert failed: The maximum number of POPs already exist.";
$lang_str['err_pop_without_funct'] =            "[Error Code 136] - Operation failed: I/S-CSCF or SSR-RM function have to be assigned to the POP";
$lang_str['err_pop_no_rt_or_hm'] =              "[Error Code 137] - Operation failed: Registration Type and HSS Mode have to be selected for POP with I/S-CSCF functions";
$lang_str['err_max_pop_per_ne'] =               "[Error Code 121] - Insert failed: The maximum number of POPs already exist.";
$lang_str['err_pop_already_assigned'] =         "[Error Code 5022] - Delete failed: The selected POP is referenced by at least one EagleXG server.";
$lang_str['err_pop_assign_subs'] =              "[Error Code 5023] - Delete failed: The selected POP is referenced by at least one subscription.";

        
$lang_str['msg_pop_route_created_s'] =          "Route Service created";
$lang_str['msg_pop_route_created_l'] =          "New Route Service has been created";

$lang_str['msg_pop_route_deleted_s'] =          "Route Service deleted";
$lang_str['msg_pop_route_deleted_l'] =          "Route Service has been deleted";

$lang_str['msg_pop_commit_s'] =                 "Changes activated";
$lang_str['msg_pop_commit_l'] =                 "Changes in POP services has been translated to flexroute.";


// Modified for PR 118789
$lang_str['fe_field_value_doesnot_exist'] =       "[Error Code 070] - Operation Failed: The selection for field name no longer exists";

/* ------------------------------------------------------------*/
/*      3rd party registration                                 */
/* ------------------------------------------------------------*/

$lang_str['fe_not_valid_notif_name'] = 			"[Error Code 6044] - Invalid value entered. A Notification Name must be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum of 32 characters";
$lang_str['fe_not_valid_notif_uri'] = 			"[Error Code 6045] - Invalid value entered. Notification URI is not valid. Please verify that the Notification URI is compliant with URI definition in http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['capacity_notif_reached'] = 			"[Error Code 6068] - Operating failed. Maximum number of 3rd Party Notifications have already been created.";
$lang_str['fe_notif_already_exists'] =			"[Error Code 6088] - Invalid value entered. Notification service with this Notification Name already exists";
$lang_str['fe_notif_to_edit_not_exists'] = 		"[Error Code 6089] - Operating failed. Notification service you selected does not exists no more. May be someone deleted it concurrently.";


$lang_str['fe_not_valid_validation_name'] = 	"[Error Code 6042] - Invalid value entered. A Validation Name must be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum of 32 characters";
$lang_str['fe_not_valid_validation_uri'] = 		"[Error Code 6043] - Invalid value entered. Validation URI is not valid. Please verify that the Validation URI is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['capacity_validations_reached'] = 	"[Error Code 6067] - Operating failed. Maximum number of 3rd Party Validations have already been created.";
$lang_str['fe_validation_already_exists'] =		"[Error Code 6090] - Invalid value entered. Validation service with this Validation Name already exists";
$lang_str['fe_validation_to_edit_not_exists'] =	"[Error Code 6091] - Operating failed. Validation service you selected does not exists no more. May be someone deleted it concurrently.";

$lang_str['msg_notification_added_s'] = 		"Notification created";
$lang_str['msg_notification_added_l'] = 		"New notification has been created";
		
$lang_str['msg_notification_deleted_s'] = 		"Notification deleted";
$lang_str['msg_notification_deleted_l'] = 		"Notification has been deleted";

$lang_str['msg_validation_added_s'] = 			"Validation created";
$lang_str['msg_validation_added_l'] = 			"New validation has been created";
		
$lang_str['msg_validation_deleted_s'] = 		"Validation deleted";
$lang_str['msg_validation_deleted_l'] = 		"Validation has been deleted";

$lang_str['msg_service_commit_s'] = 			"Changes activated";
$lang_str['msg_service_commit_l'] = 			"Changes in services has been activated";


/* ------------------------------------------------------------*/
/*      PSI                                                    */
/* ------------------------------------------------------------*/

$lang_str['fe_not_valid_psi_name'] = 			"[Error Code 6036] - Invalid value entered. Service Name is not valid. A Name must be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum length of 32 characters.";
$lang_str['fe_not_valid_psi_r_uri'] = 			"[Error Code 6025] - Invalid value entered. Public Service Identifier is not valid. Please verify that the Public Service Identifier is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['fe_not_valid_psi_uri'] = 			"[Error Code 6027] - Invalid value entered. Application Service URI is not valid. Please verify that the Application Service URI is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['capacity_psi_reached'] = 			"[Error Code 6066] - Operating failed. Maximum number of Public Services have already been created.";
$lang_str['fe_not_valid_psi_desc'] = 			"[Error Code 6092] - Invalid value entered. Description is not valid. A Description must be any string containing any printable character and a maximum length of 255 characters.";
$lang_str['fe_psi_already_exists'] = 			"[Error Code 6093] - Invalid value entered. Public service with this Service Name already exists";
$lang_str['fe_psi_to_edit_not_exists'] = 		"[Error Code 6094] - Operating failed. Public service you selected does not exists no more. May be someone deleted it concurrently.";

$lang_str['msg_service_added_s'] = 				"Service created";
$lang_str['msg_service_added_l'] = 				"New service has been created";
		
$lang_str['msg_service_deleted_s'] = 			"Service deleted";
$lang_str['msg_service_deleted_l'] = 			"Service has been deleted";


/* ------------------------------------------------------------*/
/*      IFC                                                    */
/* ------------------------------------------------------------*/


$lang_str['fe_not_valid_ifc_service_name'] =	"[Error Code 6036] - Invalid value entered. Service Name is not valid. A Name must be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum length of 32 characters.";
$lang_str['fe_not_valid_ifc_uri'] = 			"[Error Code 6019] - Invalid value entered.  Service URI is not valid. Please verify that the Service URI is compliant with URI definition in section 19.1 of the http://www.ietf.org/rfc/rfc3261.txt document";
$lang_str['fe_not_valid_ifc_priority'] = 		"[Error Code 6020] - Invalid value entered. Priority is not valid. It must be numeric within the range 1   999.";
$lang_str['err_ifc_used'] = 					"[Error Code 6037] - Operation failed. Provider Service can not be deleted when associated with any Public ID.";
$lang_str['fe_ifc_already_exists'] = 			"[Error Code 6095] - Invalid value entered. Provider service with this Service Name already exists";
$lang_str['capacity_ifc_reached'] = 			"[Error Code 6096] - Operating failed. Maximum number of Provider Services have already been created.";
$lang_str['fe_not_valid_ifc_desc'] = 			"[Error Code 6097] - Invalid value entered. Description is not valid. A Description must be any string containing any printable character and a maximum length of 255 characters.";
$lang_str['fe_ifc_without_sc'] = 				"[Error Code 6103] - Invalid value entered. Provider service must be associated with one or more Subscriber Classes.";

$lang_str['fe_ifc_crit_r_uri_not_valid'] = 		"[Error Code 6038] - Invalid value entered. Requested URI is not valid. A Requested URI must be any string containing either [a-z],[A-Z],[0-9], colon (:), at (@), and period (.) and a maximum length of 128 characters.";
$lang_str['fe_ifc_crit_invalid_p_header'] = 	"[Error Code 6039] - Invalid value entered. The SIP header presence value is not valid. A SIP header presence value must be any string containing any printable US-ASCII characters (i.e., characters that have values between 33 and 126, inclusive), except colon and a maximum length of 128 characters.";
$lang_str['fe_ifc_crit_invalid_v_header'] = 	"[Error Code 6040] - Invalid value entered. The SIP header value is not valid. A SIP header value must be any string containing any printable US-ASCII characters (i.e., characters that have values between 33 and 126, inclusive), except colon and a maximum length of 128 characters.";
$lang_str['fe_ifc_crit_r_group_full'] = 		"[Error Code 6041] - Insert Failed. Each Rule Group may contain a maximum of five Rules.";
$lang_str['fe_ifc_crit_exists_in_group'] = 		"[Error Code 6098] - Insert Failed. Rule of same type already exists in specified Rule Group";


$lang_str['fe_not_valid_priority'] = 			"Priority is not valid number";
$lang_str['fe_empty_value'] = 					"You must enter some value";
$lang_str['fe_empty_header_name'] = 			"You must enter name of header";
$lang_str['fe_empty_new_list_name'] = 			"You must enter name of list";
$lang_str['fe_list_not_choosed'] = 				"You have to choose the list";
$lang_str['fe_list_already_exists'] = 			"List with this name already exists";
$lang_str['fe_not_valid_list_name'] = 			"Name of list is not valid. Allowed chars are: A-Z, 0-9, and '_'";
$lang_str['fe_list_not_found_1'] = 				"List named";
$lang_str['fe_list_not_found_2'] = 				"not found";

$lang_str['msg_criterion_created_s'] = 			"Criterion created";
$lang_str['msg_criterion_created_l'] = 			"New criterion has been created";
		
$lang_str['msg_criterion_deleted_s'] = 			"Criterion deleted";
$lang_str['msg_criterion_deleted_l'] = 			"Criterion has been deleted";

$lang_str['msg_rule_group_deleted_s'] = 		"Rule group deleted";
$lang_str['msg_rule_group_deleted_l'] = 		"Rule group has been deleted";



/* ------------------------------------------------------------*/
/*      Domains                                                */
/* ------------------------------------------------------------*/

//$lang_str['fe_not_valid_domainname'] = 			"[Error Code 6079] - Invalid value entered. Domain Name is not valid.";
$lang_str['fe_not_valid_domainname'] = 			"[Error Code 7023] - Invalid characters in input. Allowed characters are [0-9], [a-z], '.' and '-'";
$lang_str['fe_empty_domainname'] =              "[Error Code 7021] - A value must be entered";
$lang_str['fe_domain_not_valid_fqdn'] =         "[Error Code 7025] - Invalid FQDN name";
$lang_str['fe_domain_is_used'] = 				"[Error Code 6080] - Operation Failed. Domain in use.";
$lang_str['fe_domain_already_exists'] = 		"[Error Code 6081] - Invalid value entered. Domain with this name already exists";
$lang_str['domain_no_auth'] =                   "SIP requests from user agents pretending to be subscribed to this domain will not be challenged. Do you want to proceed without authentication?";
// Modified for PR 123701
// Error code changed as per FD
$lang_str['capacity_domain_reached'] = 			"[Error Code 128] - Insert failed: The maximum number of Domains already exist.";

$lang_str['domain_auth_static_pop'] =           "Authentication mode has no effect on statically registered subscribers.";

$lang_str['msg_domain_added_s'] = 				"Domain created";
$lang_str['msg_domain_added_l'] = 				"New domain has been created";
		
$lang_str['msg_domain_deleted_s'] = 			"Domain deleted";
$lang_str['msg_domain_deleted_l'] = 			"Domain has been deleted";

// string identificating domain for global tel uris - does not need to be translated
$lang_str['global_tel_uri_domain'] = 			"<global tel #>";



/* ------------------------------------------------------------*/
/*      Subscriptions                                          */
/* ------------------------------------------------------------*/

$lang_str['fe_subscriber_not_valid_name'] = 	"[Error Code 6046] - Invalid value entered. A Subscription ID must begin with a letter and be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a minimum of 6 and a maximum of 60 characters";
$lang_str['fe_subscription_already_exists'] = 	"[Error Code 6057] - Subscription ID already exists.";
$lang_str['limit_privID_subs_reached'] = 		"[Error Code 6052] - The maximum number of Private IDs per Subscription can not be exceeded.";
$lang_str['capacity_subscription_reached'] = 	"[Error Code 6056] - Insert failed. The maximum number of Subscriptions have already been created.";
$lang_str['invalid_implicit_group'] = 			"[Error Code 6099] - Invalid value entered. Implicit group is not valid";
$lang_str['fe_create_subscr_not_alllowed'] = 	"[Error Code 6100] - Operating failed. Subscription can not be created. Either no POP or no subscriber class or no domain exists. Please create them first";

$lang_str['fe_private_id_already_exists'] = 	"[Error Code 6058] - Private ID already exists.";
$lang_str['fe_priv_id_not_valid'] = 			"[Error Code 6047] - Invalid value entered. A Private ID can be any string containing either [a-z], [A-Z], [0-9] or an '_' (Under score) and a maximum of 64 characters";
$lang_str['fe_priv_id_domain_not_selected'] = 	"[Error Code 6060] - Private ID - Domain must be selected.";
$lang_str['fe_priv_id_not_valid_pass'] = 		"[Error Code 6031] - Invalid value entered. A Password can be any string starting with an alphabet and containing either [a-z], [A-Z], [0-9] or an '_' (Under score). The Password must have at least one number and one uppercase letter. Length of Password must be a minimum of 6 and a maximum of 12 characters.";
$lang_str['fe_priv_id_sc_not_selected'] =		"[Error Code 6062] - Private ID - Subscriber Class must be selected.";
$lang_str['fe_priv_id_pop_not_selected'] = 		"[Error Code 6061] - Private ID - POP must be selected.";
$lang_str['capacity_privID_reached'] = 			"[Error Code 6063] - Operation failed. Maximum number of Private IDs have already been created.";
$lang_str['ERR_NO_SCSCF_IN_POP'] =	 			"[Error Code 124] - Insert failed: A subscriber private-id cannot be assigned to a POP that has no I/S-CSCF.";
$lang_str['err_hss_pop_dom'] =                  "[Error Code 6105] - Insert failed: A subscriber private-id cannot be assigned to a POP that is in HSS mode and domain that is hosted by HSS.";

$lang_str['fe_pub_id_not_valid_sip'] = 			"[Error Code 6048] - Invalid value entered. SIP URI is not valid.  Valid characters are [a-z],[A-Z],[0-9], and period (.) and a maximum length of 128 characters.";
$lang_str['fe_pub_id_not_valid_tel'] = 			"[Error Code 6049] - Invalid value entered. TEL URI is not valid.  Valid characters are [0-9] and a maximum length of 21 characters.";
$lang_str['fe_pub_id_already_exists'] = 		"[Error Code 6059] - Public ID already exists.";
$lang_str['fe_pub_id_no_sip_uri_in_ig'] = 		"[Error Code 6050] - Operation Failed. A SIP Public ID for the given Implicit Group must be saved before a TEL Public ID can be saved.";
$lang_str['fe_no_route_for_static_subs'] = 		"[Error Code 5052] - Insert failed:  No static route exists for the specified subscriber public identity tel.";
$lang_str['fe_pub_id_per_ig_exceeds_limit'] = 	"[Error Code 6054] - The maximum number of Public IDs per Implicit Set can not be exceeded.";
$lang_str['limit_pubID_privID_reached'] = 		"[Error Code 6053] - The maximum number of Public IDs per Private ID can not be exceeded.";
$lang_str['capacity_pubID_reached'] = 			"[Error Code 6064] - Operation failed. Maximum number of Public IDs have already been created.";

// Added -- For Static Registration PR # 113207
$lang_str['fe_public_id_already_exists_sip'] =  "[Error Code 5100] - Insert failed: The specified public identity sip already exists for another subscriber.";
$lang_str['fe_not_valid_public_id_sip']      = 	"[Error Code 5106] - Insert failed: Public ID sip is not valid.";
$lang_str['fe_not_valid_public_id_tel']      = 	"[Error Code 5107] - Insert failed: Public ID tel is not valid.";
$lang_str['fe_public_id_already_exists_tel'] = 	"[Error Code 5101] - Insert failed: The specified public identity tel already exists for another subscriber.";
$lang_str['fe_insert_no_sip_uri_in_ig']      = 	"[Error Code 5104] - Insert failed: At least one SIP URI must be provisioned in the implicit group in order to provision a TEL URI.";
$lang_str['fe_edit_no_sip_uri_in_ig']        =  "[Error Code 5105] - Edit failed: At least one SIP URI must be provisioned in the implicit group in order to provision a TEL URI.";
$lang_str['fe_edit_tel_exists_in_ig']        =  "[Error Code 5103] - Edit failed: This is the last SIP URI provisioned for TEL URI in this implicit group.";
$lang_str['fe_delete_tel_exists_in_ig']      =  "[Error Code 5102] - Delete failed: This is the last SIP URI provisioned for TEL URI in this implicit group.";
// End -- For Static Registration PR # 113207

// Added for PR # 118377

$lang_str['fe_static_pop_with_hss_domain']   =  " [Error Code 5053] - Insert failed:  POP attribute 'static registration' is not compatible with domain attribute 'hosted by HSS'.";

$lang_str['msg_pui_created_s'] = 				"Public ID created";
$lang_str['msg_pui_created_l'] = 				"New public ID has been created";

$lang_str['msg_pui_deleted_s'] = 				"Public ID deleted";
$lang_str['msg_pui_deleted_l'] = 				"Public ID has been deleted";

$lang_str['msg_pri_created_s'] = 				"Private ID created";
$lang_str['msg_pri_created_l'] = 				"New private ID has been created";

$lang_str['msg_pri_deleted_s'] = 				"Private ID deleted";
$lang_str['msg_pri_deleted_l'] = 				"Private ID has been deleted";

$lang_str['msg_sc_created_s'] = 				"Subscription created";
$lang_str['msg_sc_created_l'] = 				"New subscription has been created";

$lang_str['msg_sc_deleted_s'] = 				"Subscription deleted";
$lang_str['msg_sc_deleted_l'] = 				"Subscription has been deleted";


/* ------------------------------------------------------------*/
/*      attributes                                             */
/* ------------------------------------------------------------*/

$lang_str['fe_invalid_value_of_attribute'] = 	"invalid value of attribute";
$lang_str['fe_is_not_number'] = 				"is not valid number";
$lang_str['fe_is_not_sip_adr'] = 				"is not valid sip address";
$lang_str['no_attributes_defined'] = 			"No attributes defined by admin";

$lang_str['ff_send_daily_missed_calls'] =		"send me daily my missed calls to my email";

$lang_str['ff_uri_def_f'] =						"default flags for uri";
$lang_str['ff_credential_def_f'] =				"default flags for credentials";
$lang_str['ff_domain_def_f'] =					"default flags for domain";

/* ------------------------------------------------------------*/
/*      attribute types                                        */
/* ------------------------------------------------------------*/

$lang_str['fe_not_filled_name_of_attribute'] = 	"you must fill attribute name";
$lang_str['ff_order'] = 						"order";
$lang_str['ff_att_name'] = 						"attribute name";
$lang_str['ff_att_type'] = 						"attribute type";
$lang_str['ff_label'] = 						"label";
$lang_str['ff_att_user'] = 						"user";
$lang_str['ff_att_domain'] = 					"domain";
$lang_str['ff_att_global'] = 					"global";
$lang_str['ff_multivalue'] = 					"multivalue";
$lang_str['ff_att_reg'] = 						"required on registration";
$lang_str['ff_att_req'] = 						"required (not empty)";
$lang_str['ff_fr_timer'] = 						"final response timer";
$lang_str['ff_fr_inv_timer'] = 					"final response invite timer";
$lang_str['ff_uid_format'] = 					"format of newly created UIDs";
$lang_str['ff_did_format'] = 					"format of newly created DIDs";



$lang_str['th_att_name'] = 						"attribute name";
$lang_str['th_att_type'] = 						"attribute type";
$lang_str['th_order'] = 						"order";
$lang_str['th_label'] = 						"label";
$lang_str['fe_order_is_not_number'] = 			"'order' is not valid number";

$lang_str['fe_not_filled_item_label'] = 		"you must fill item label";
$lang_str['fe_not_filled_item_value'] = 		"you must fill item value";
$lang_str['ff_item_label'] = 					"item label";
$lang_str['ff_item_value'] = 					"item value";
$lang_str['th_item_label'] = 					"item label";
$lang_str['th_item_value'] = 					"item value";
$lang_str['l_back_to_editing_attributes'] = 	"back to editing attributes";
$lang_str['realy_want_you_delete_this_attr'] = 	"Realy want you delete this attribute?";
$lang_str['realy_want_you_delete_this_item'] = 	"Realy want you delete this item?";


$lang_str['attr_type_warning'] = 				"On this page you may define new attributes and change types of them, their flags, etc. Preddefined attributes are mostly used internaly by SerWeb or by SER. Do not change them if you do not know what are you doing!!!";
$lang_str['at_hint_order'] = 					"Attributes are arranged in this order in SerWeb";
$lang_str['at_hint_label'] = 					"Label of attribute displayed in SerWeb. If starts with '@', the string is translated into user language with files in directory 'lang'. It is your responsibility that all used phrases are present in files for all languages.";
$lang_str['at_hint_for_ser'] = 					"Attribute is loaded by SER. Only newly created attributes are affected by change of this.";
$lang_str['at_hint_for_serweb'] = 				"Attribute is loaded by SerWeb. Only newly created attributes are affected by change of this.";
$lang_str['at_hint_user'] = 					"Attribute is displayed on user preferences page";
$lang_str['at_hint_domain'] = 					"Attribute is displayed on domain preferences page";
$lang_str['at_hint_global'] = 					"Attribute is displayed on global preferences page";
$lang_str['at_hint_multivalue'] = 				"Attribute may have multiple values";
$lang_str['at_hint_registration'] = 			"Attribute is displayed on user registration form";
$lang_str['at_hint_required'] = 				"Attribute has to have any not empty value. Not used for all types. Used for types: int, email_adr, sip_adr, etc.";


$lang_str['ff_att_default_value'] = 			"default value";
$lang_str['th_att_default_value'] = 			"default value";
$lang_str['ff_set_as_default'] = 				"set as default";
$lang_str['edit_items_of_the_list'] = 			"edit items of the list";

$lang_str['o_lang_not_selected'] = 				"not selected";
?>
