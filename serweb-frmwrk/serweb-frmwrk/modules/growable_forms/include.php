<?php
/**
 *	File automaticaly included by the framework when module is loaded
 * 
 *	@author     Karel Kozlik
 *	@package    serweb
 *	@subpackage mod_growable_forms
 */ 

require_once( dirname(__FILE__)."/classes.php" );

// load the javascript if controler object already exists
if (isset($GLOBALS['controler'])){	
    $GLOBALS['controler']->add_required_javascript('core/functions.js');
    $GLOBALS['controler']->add_required_javascript('core/get_js.php?mod=growable_forms&js=growable_forms.js');
}
	
?>
