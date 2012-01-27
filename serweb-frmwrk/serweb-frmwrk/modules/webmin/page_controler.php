<?php

class webmin_page_conroler extends page_conroler{

    /**
     *  Return URL that access given javascript file to given module.
     *  Module directories are not usualy accessible via html directory tree.
     *  So there is getter script inside the javascript directory that access the
     *  javascript file and return its content to HTML browser.
     *
     *  This method overrides the one from generic page_controler to use 
     *  .cgi getter which only works with webmin webserwer.     
     *           
     *  @param  string  $module     The module from which we require a javascript file                            
     *  @param  string  $file       The filename of the required file                            
     */
    function js_from_mod_getter($module, $file){
        return "get_js.php.cgi?mod=".rawurlencode($module).
                             "&js=".rawurlencode($file);
    }

    /**
     *  Return URL that access given css file from templates directory.
     *  Templates directory is not usualy accessible via html directory tree.
     *  So there is getter script inside the styles directory that access the
     *  css file and return its content to HTML browser.
     * 
     *  This method overrides the one from generic page_controler to use 
     *  .cgi getter which only works with webmin webserwer.     
     *           
     *  @param  string  $file       The filename of the required file with path
     *                              relatively to templates directory                                 
     */
    function css_from_tpl_getter($file){
        global $config;
        return $config->style_src_path."get_css.php.cgi?css=".RawURLEncode($file);
    }
}

?>
