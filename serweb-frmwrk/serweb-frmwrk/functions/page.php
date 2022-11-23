<?php
/**
 *  Functions for output basic page layout
 *
 *  @author     Karel Kozlik
 *  @version    $Id: page.php,v 1.37 2008/01/09 15:25:59 kozlik Exp $
 *  @package    serweb
 *  @subpackage framework
 */

/**
 *  Put HTTP headers
 */

function put_headers(){
    Header("Pragma:  no-cache");
    Header("Cache-Control: no-cache");
    Header("Expires: ".GMDate("D, d M Y H:i:s")." GMT");
}

/**
 *  Print begin of html document
 *
 *  Started by <!DOCTYPE....>
 *  flowed <html><head>.....
 *  and ending </head>
 *
 *  @param  array   $parameters associative array containing info about page
 */

function print_html_head($parameters=array()){
    global $config, $lang_set, $controler;

    if (empty($parameters['html_title'])) $title = $config->html_title;
    else                                  $title = $parameters['html_title'];

    header("Content-Type: text/html; charset=".$lang_set['charset']);

    if (isset($parameters['doc_type'])) echo $parameters['doc_type']."\n";
    elseif ($config->html_doctype=='strict'){
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
    }elseif ($config->html_doctype=='transitional'){
        ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?php
    }elseif ($config->html_doctype){
        echo "<!DOCTYPE {$config->html_doctype}>\n";
    }

    $lang = "";
    if (!empty($lang_set['lang_code'])) $lang = "lang='{$lang_set['lang_code']}'";

    echo "<html $lang>\n<head>\n";

    if ($title) echo "    <title>".$title."</title>\n";
?>

    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang_set['charset'];?>" />
<?php
    if (!empty($parameters['author_meta_tag'])) {
    echo "    <meta name=\"Author\" content=\"".$parameters['author_meta_tag']."\" />\n";
    } ?>
    <meta http-equiv="PRAGMA" content="no-cache" />
    <meta http-equiv="Cache-control" content="no-cache" />
    <meta http-equiv="Expires" content="<?php echo GMDate("D, d M Y H:i:s")." GMT";?>" />

<?php
    if (isset($parameters['html_headers']) and is_array($parameters['html_headers'])){
        foreach($parameters['html_headers'] as $v) echo $v."\n";
    }

    if (!empty($parameters['css_file'])){
        if (is_array($parameters['css_file'])){
            foreach($parameters['css_file'] as $v)
                echo '    <LINK REL="StyleSheet" HREF="'.htmlspecialchars($v).'" TYPE="text/css" nonce="'.$parameters['nonce'].'"/>'."\n";
        }
        else{
            echo '    <LINK REL="StyleSheet" HREF="'.htmlspecialchars($parameters['css_file']).'" TYPE="text/css" nonce="'.$parameters['nonce'].'"/>'."\n";
        }
    }

    if (!empty($parameters['ie_selects'])){
        // Workaround for ability to enable/disable options of selects.
        // It is not needed since IE8 as IE finaly support it by itself.
        echo '    <!--[if lt IE 8]><LINK REL="StyleSheet" HREF="'.htmlspecialchars($config->style_src_path."core/ie_select.css.php").'" TYPE="text/css" /><![endif]-->'."\n";
    }

    if (isset($parameters['required_javascript']) and is_array($parameters['required_javascript'])){
        foreach($parameters['required_javascript'] as $v) {
            echo '    <script type="text/javascript" src="'.htmlspecialchars($v).'" nonce="'.$parameters['nonce'].'"></script>'."\n";
        }
    }

    if (isset($config->html_headers) and is_array($config->html_headers)){
        foreach($config->html_headers as $v) echo $v."\n";
    }

    echo "</head>\n";

} //end function print_html_head()



/**
 *  Print begin of html body
 *
 *  This function should be replaced by smarty template
 *
 *  @param array $parameters associative array containing info about page
 *  @deprecated
 */

function print_html_body_begin(&$parameters){
    global $config, $auth, $errors, $message;

    if (!$parameters) $parameters=null;

    echo "<body>\n";

    // call user defined function at html body begin
    if (isset($parameters['run_at_html_body_begin']) and function_exists($parameters['run_at_html_body_begin']))
        $parameters['run_at_html_body_begin']($parameters);

    if (isset($parameters['prolog'])) echo $parameters['prolog'];
    else virtual($config->html_prolog);

    if (isset($parameters['title']) and $parameters['title']) echo $parameters['title'];

    if (isset($parameters['separator'])) echo $parameters['separator'];
    else virtual($config->html_separator);

    echo "\n<div class=\"swMain\">\n";

    return;

} //end function print_html_body_begin


/**
 *  Print end of html body
 *
 *  This function should be replaced by smarty template
 *
 *  @deprecated
 */

function print_html_body_end(&$parameters){
    global $config, $_page_tab;

    echo "</div><!-- swMain -->\n";

    if (isset($parameters['epilog'])) echo $parameters['epilog'];
    else virtual($config->html_epilog);

    echo "</body>\n";
}
