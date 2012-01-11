<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty {html_tabs} function plugin
 *
 * Type:     function<br>
 * Name:     html_tabs<br>
 * Date:     Jun 6, 2004<br>
 * Purpose:  return tabs<br>
 * Input:<br>
 *         - tabs = array of tab objects (required)
 *         - path = path to pages (optional, default "")
 *         - selected = selected tab (optional, default actual page)
 *         - no_select = no tab is selected (optional, default false)
 *         - anchor_extra_html = extra html into <A> tags (optional , default '')
 *         - div_id = ID attr of main div element (optional, default "swTabs")
 *         - div_class = CLASS attr of main div element (optional, default none)
 *
 * Examples: {html_tabs tabs=$tabs}
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_html_tabs($params, &$smarty){
	global $controler, $_SERVER;
    
    $path = '';
    $selected = NULL;
    $no_select = false;
	$anchor_extra_html = '';
	$div_class = '';

    extract($params);
	
    if (empty($tabs)) {
        $smarty->trigger_error("html_tabs: missing 'tabs' parameter", E_USER_NOTICE);
        return;
    }
	
	if (!$selected){
		$selected=basename($_SERVER['SCRIPT_FILENAME']);
	}

	if (!isset($div_id)) $div_id="swTabs";

	$out = '<div ';
	if ($div_id)    $out .= 'id="'.$div_id.'" ';
	if ($div_class) $out .= 'class="'.$div_class.'" ';
	$out .= '><ul>';

	foreach($tabs as $i => $value){
		if ($value->is_enabled()){
			if ($value->get_page()==$selected and !$no_select){
				$out.='<li class="swActiveTab"><div class="swTabsL"></div><strong><span>'.$value->get_name().'</span></strong><div class="swTabsR"></div></li>';
			}
			else{
				$out.='<li><div class="swTabsL"></div><a href="'.htmlspecialchars($controler->url($path.$value->get_page()), ENT_QUOTES).'" '.$anchor_extra_html.' class="tabl"><span>'.$value->get_name().'</span></a><div class="swTabsR"></div></li>';
			}//if ($value->get_page()==$selected)
		}// if ($value->is_enabled())
	} //foreach		
	
	$out.='</ul></div>';

	return $out;
}

/* vim: set expandtab: */

?>
