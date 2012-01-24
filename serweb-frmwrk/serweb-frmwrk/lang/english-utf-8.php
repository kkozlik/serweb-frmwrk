<?php
/**
 * Prefixes:
 * 'msg'    - message 
 * 'b'      - button
 * 'l'      - link
 * 'err'    - error
 */

$lang_set['charset'] =          "utf-8";
$lang_set['date_time_format'] = "Y-m-d H:i";
$lang_set['date_format'] =      "Y-m-d";
$lang_set['time_format'] =      "H:i";


$lang_str['msg_changes_saved'] =                "Your changes have been saved";

$lang_str['b_submit'] =                         "Save";
$lang_str['b_apply'] =                          "Apply";
$lang_str['b_cancel'] =                         "Cancel";
$lang_str['b_search'] =                         "Search";
$lang_str['b_ok'] =                             "OK";
$lang_str['b_reset'] =                          "Reset";



/* ------------------------------------------------------------*/
/*      error messages                                         */
/* ------------------------------------------------------------*/

$lang_str['err_max_entries'] =                  "[Error Code 0001] - Maximum number of <name> has already been created.";
$lang_str['err_dup_assign'] =                   "[Error Code 0001] - The <object1> is already assigned to the <object2>.";
$lang_str['err_in_use'] =                       "[Error Code 0002] - The <object1> can not be deleted as long as it is used by the <object2>.";
$lang_str['err_empty_value'] =                  "[Error Code 0003] - A value for the <name> must be entered.";
$lang_str['err_dup_entry'] =                    "[Error Code 0004] - The <object> with the same <name> already exists.";
$lang_str['err_missing_entry'] =                "[Error Code 0005] - The <object> no longer exists in the system.";
$lang_str['err_value_not_selected'] =           "[Error Code 0006] - The <name> must be selected.";
$lang_str['err_greater_than'] =                 "[Error Code 0007] - The <var1> must be greater than the <var2>.";
$lang_str['err_desc_order_list'] =              "[Error Code 0008] - The input values of the <var_list> shall be in a strictly descending order.";
$lang_str['err_out_of_range'] =                 "[Error Code 0009] - The input value of the <name> is outside the valid range <min> - <max>.";
$lang_str['err_invalid_regexp'] =               "[Error Code 0100] - The input value \"#VALUE#\" for the <name> is not a valid regular expression.";
$lang_str['err_invalid_number'] =               "[Error Code 0101] - The input value \"#VALUE#\" for the <name> is not a valid number.";
$lang_str['err_invalid_ip_addr'] =              "[Error Code 0103] - The input value \"#VALUE#\" for the <name> is not a valid IPv4 address.";
$lang_str['err_invalid_sip_uri'] =              "[Error Code 0104] - The input value \"#VALUE#\" for the <name> is not a valid SIP URI.";
$lang_str['err_invalid_ip_addr_port'] =         "[Error Code 0105] - The input value \"#VALUE#\" for the <name> is not a valid {IPv4 address}:{port number}.";
$lang_str['err_invalid_ip_addr_fqdn'] =         "[Error Code 0106] - The input value \"#VALUE#\" for the <name> is neither a valid IPv4 address nor a valid fully qualified domain name.";
$lang_str['err_invalid_ip_addr_netmask'] =      "[Error Code 0107] - The input value \"#VALUE#\" for the <name> is not a valid {IP address}/{netmask}.";
$lang_str['err_invalid_phone_number'] =         "[Error Code 0108] - The input value \"#VALUE#\" for the <name> is not a valid telephone number.";
$lang_str['err_invalid_obj_name'] =             "[Error Code 0109] - The input value \"#VALUE#\" for the <name> is not a valid object name.";
$lang_str['err_invalid_fqdn'] =                 "[Error Code 0110] - The input value \"#VALUE#\" for the <name> is not a valid fully qualified domain name.";
$lang_str['err_invalid_fqdn_ip_ipport'] =       "[Error Code 0111] - The input value \"#VALUE#\" for the <name> is neither a valid IPv4 address nor a valid <IP address>:<port number> nor a valid fully qualified domain name.";

?>
