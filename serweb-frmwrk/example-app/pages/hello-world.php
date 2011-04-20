<?php

$_phplib_page_open = array("sess" => "phplib_Session");

$_data_layer_required_methods=array();
$_required_modules = array('hello-world');
$_required_apu = array('apu_hello_world'); 

require dirname(__FILE__)."/prepend.php";


$apu    = new apu_hello_world();

//$page_attributes['css_file'][]=$config->style_src_path."get_css.php?css=".RawURLEncode("hello-world/hw.css");

$controler->add_apu($apu);
//$controler->add_reqired_javascript('core/functions.js'); 
$controler->set_template_name('hello-world/hw.tpl');
$controler->start();


?>
