<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty add_html_attrib_to_tag modifier plugin
 *
 * Type:     modifier<br>
 * Name:     add_html_attrib_to_tag<br>
 * Date:     Feb 24, 2003
 * Purpose:  for variables that contain html tag, add new attribute to them
 * Input:    html tag to which should be attrib added
 * Example:  {$var|cat:"onclick='foo();'"}
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version 1.0
 * @param string
 * @param string
 * @param bool   - true value can be useful for more complex tags (as 'select' is) place for add attribs is serching for from begining
 * @return string
 */
function smarty_modifier_add_html_attrib_to_tag($string, $attribs, $from_beginning=false)
{
	if ($from_beginning){
		/* put attribs after tag name - form complex tabs as <select ... > .... </select> */
		if (!ereg("^([^<]*<[[:blank:]]*[a-zA-Z_]+)(.*)$", $string, $regs)){
			/* ereg doesn't match, return unchanged */
			return $string;
		}

		$str1 = $regs[1];
		$str2 = $regs[2];
	}
	else{
		/* put attribs before last '>' - for simple tags as <input ... > is*/
		$pos = strrpos($string, ">");
		/* if string isn't html tag, return it unchanged */
		if ($pos === 'false') return $string;

		$str1 = substr($string, 0, $pos);
		$str2 = substr($string, $pos);
	}
	
	
    return $str1.' '.$attribs.' '.$str2;
}

/* vim: set expandtab: */

?>
