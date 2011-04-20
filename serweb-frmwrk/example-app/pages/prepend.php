<?php
/**
 *  File required by all pages. It is used to load all required files
 * 
 */ 


$thisdir = dirname(__FILE__);
require_once($thisdir."/../../serweb-frmwrk/functions/bootstrap.php");

$GLOBALS['page_attributes']=array(
    'title' => null,
    'html_title' => "Tekelec Application Server",
//  'tab_collection' => $config->admin_tabs,
//  'path_to_pages' => $config->admin_pages_path,
//  'run_at_html_body_begin' => '_disable_unneeded_tabs',
    'logout'=>false,
    'prolog'=>"",
    'separator'=>"",
    'epilog'=>"",
    'ie_selects' => true,
    'css_file'=>array($config->style_src_path."styles.css")
);



?>
