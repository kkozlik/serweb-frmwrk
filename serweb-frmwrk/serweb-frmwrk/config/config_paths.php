<?
/*
 * $Id: config_paths.php,v 1.8 2006/04/14 10:58:04 kozlik Exp $
 */

global $config;

/* the web path bellow web accesible directories begin to spread; 
   Don't forget trailing slash.
   This setting depends on your apache configuration.
*/
if (!isset($config->root_path)) $config->root_path="/";

/* root uri of your server */
if (isset($_SERVER['SERVER_NAME']))
    $config->root_uri="http://".$_SERVER['SERVER_NAME'];
else
    $config->root_uri="";

/* where is your zone file on your server ? */
$config->zonetab_file =   "/usr/share/zoneinfo/zone.tab";

/* relative paths of serweb tree */
$config->img_src_path =     $config->root_path."img/";
$config->js_src_path =      $config->root_path."js/";
$config->style_src_path =   $config->root_path."styles/";
$config->user_pages_path =  null;
$config->admin_pages_path = null;
$config->domains_path =     null;

/* Directory where smarty stores compiled templates */
$config->smarty_compile_dir = "/tmp/smarty/";

/* Directory where tklc gui files are located */
$config->gui_dir = getenv('TC_GUI_DIR')."/";

/* names of HTML documents surrounding
   serweb pages -- these may typically include banner, trailers,
   and whatever else appropriate to your web design; make sure
   the values point to existing files; the files should include
   at least:
   prolog: <body> or <body><h1>, etc.
   separator: may be empty, or </h1><hr> etc.
   epilog: </body>
*/

$config->html_prolog="prolog.html";
$config->html_separator="separator.html";
$config->html_epilog="epilog.html";



/*
 * load developer config if exists
 */
 
$config_paths_developer = dirname(__FILE__) . "/config_paths.developer.php";
if (file_exists($config_paths_developer)){
    require_once ($config_paths_developer);
}
unset($config_paths_developer);
 
?>
