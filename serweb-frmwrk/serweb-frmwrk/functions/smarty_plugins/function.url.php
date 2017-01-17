<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */


/**
 * Smarty {link} function plugin
 *
 * Type:     function<br>
 * Name:     link<br>
 * Date:     Jun 2, 2004<br>
 * Purpose:  Return an URL referencing the current phplib session<br>
 * Input:<br>
 *         - url = url to which may be added session referencing param
 *
 * Examples: {url url="index.php"}
 * Output:   index.php?session_id=123456789
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_url($params, &$smarty)
{

    extract($params);

    if (empty($url)) {
        $smarty->trigger_error("url: missing 'url' parameter");
        return;
    }

	global $controler;
	return $controler->url($url);
}

/* vim: set expandtab: */


?>
