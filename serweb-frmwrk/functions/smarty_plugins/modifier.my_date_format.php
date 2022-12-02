<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */

/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     my_date_format<br>
 * Purpose:  format datestamps via date<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version 1.0
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_my_date_format($string, $format="%b %e, %Y", $default_date=null)
{

    /**
     * Include the {@link shared.make_timestamp.php} plugin
     */
    require_once(SMARTY_PLUGINS_DIR . 'shared.make_timestamp.php');

    if($string != '') {
        return date($format, smarty_make_timestamp($string));
    } elseif (isset($default_date) && $default_date != '') {
        return date($format, smarty_make_timestamp($default_date));
    } else {
        return;
    }
}

/* vim: set expandtab: */

?>
