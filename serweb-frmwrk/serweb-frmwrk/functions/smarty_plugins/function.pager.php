<?
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage customized_plugins
 */

/**
 * Smarty {pager} function plugin
 *
 * displays paging links to be able to browse in bit set of records
 *
 * Type:     function<br>
 * Name:     pager<br>
 * Date:     Jun 6, 2004<br>
 * Purpose:  create a paging output to be able to browse long lists<br>
 * Input:<br>
 *         - page = associative array containing pageing info (keys: 'pos', 'items', 'limit', 'url') (required)
 *         - link_limit = number of links to other pages (optional, default 10)
 *         - txt_prev = label of link to go to previous page (optional, default "previous")
 *         - txt_next = label of link to go to next page (optional, default "next")
 *         - txt_first = label of link to go to first page. If empty, link is not displayed. (optional, default "")
 *         - txt_last = label of link to go to last page. If empty, link is not displayed. (optional, default "")
 *         - class_num = CSS class assigned to page numbers (<A> tags) (optional, default "nav")
 *         - class_numon = CSS class assigned to number of active page (optional, default "navActual")
 *         - class_text = CSS class assigned to text labels (previous, next, etc.) (optional, default "nav")
 *         - separator = string to put between the page numbers (optional, default &nbsp;)
 *         - display = if is 'always', the pager even if there is too few items - nothing to pageing (optional , default '')
 *         - link_special_html = special html attribs for <a href=""> (optional, default "")
 *
 * Examples: {pager rowcount=$LISTDATA.rowcount limit=$LISTDATA.limit txt_first=$L_MORE class_num="fl" class_numon="fl" class_text="fl"}
 * Install:  Drop into the plugin directory
 *
 * @author   Karel Kozlik <kozlik@kufr.cz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */

  function smarty_function_pager($params, &$smarty){
      /* 
      @param    array     $page            - associative array containing next four items:
                int       'pos'            - number of first item on a page 
                int       'items'          - number of all items
                int       'limit'          - number of items on a page 
                string    'url'            - url of pages - number of first item is appended
     */

	// START INIT
	$link_limit	= 10;
	$separator    = '&nbsp;';
	$class_text   = 'nav';
	$class_num    = 'nav';
	$class_numon  = 'navActual';
	$txt_prev     = 'previous';            // previous
	$txt_next     = 'next';                // next
	$txt_first    = '';
	$txt_last     = '';
	$display      = '';
	$link_special_html = '';
	
    // Optional parameter to verify if page links are to be displayed or not
    $skip_page_links = '';

	foreach($params as $key=>$value) {
		if ($key == 'page') continue;
		$tmps[strtolower($key)] = $value;
		$tmp = strtolower($key);
		if (!(${$tmp} = $value)) {
			${$tmp} = '';
		}
	}    

	foreach($params['page'] as $key=>$value) {
		$tmps[strtolower($key)] = $value;
		$tmp = strtolower($key);
		if (!(${$tmp} = $value)) {
			${$tmp} = '';
		}
	}    
	// START data check
	$minVars = array('pos', 'items', 'limit', 'url');
	foreach($minVars as $tmp)  {
		if (!isset($params['page'][$tmp])) {
			$smarty->trigger_error('plugin "pager": missing or empty parameter: page["'.$tmp.'"]');
		}
	}

	  
	if ($items <= $limit and $display!='always') return "";
	$out="";

	$lfrom=$pos-($link_limit*$limit); if ($lfrom<0) $lfrom=0;
	$lto=$pos+(($link_limit+1)*$limit); if ($lto>$items) $lto=$items;

	if ($txt_first){
		if ($pos>0) $out.='<a href="'.htmlspecialchars(str_replace("<pager>", 0, $url), ENT_QUOTES).'" class="'.$class_text.'" '.$link_special_html.'>'.$txt_first.'</a>'.$separator;
		elseif($display=='always') $out.='<span class="'.$class_text.'">'.$txt_first.'</span>'.$separator;
	}

    // If page links are not the displayed
    if(true == $skip_page_links)
    {
        if ($pos>0) $out.='<a href="'.htmlspecialchars(str_replace("<pager>", ((($pos-$limit)>0)?($pos-$limit):0), $url), ENT_QUOTES).'" class="'.$class_text.'" '.$link_special_html.'>'.$txt_prev.'</a>';
        elseif($display=='always') $out.='<span class="'.$class_text.'">'.$txt_prev.'</span>';

    }
    else
    {
        if ($pos>0) $out.='<a href="'.htmlspecialchars(str_replace("<pager>", ((($pos-$limit)>0)?($pos-$limit):0), $url), ENT_QUOTES).'" class="'.$class_text.'" '.$link_special_html.'>'.$txt_prev.'</a>'.$separator;
        elseif($display=='always') $out.='<span class="'.$class_text.'">'.$txt_prev.'</span>'.$separator;

        // skip printing page links
        for ($i=$lfrom; $i<$lto; $i+=$limit){
            /* do not add separateor before first number */

            if ($i != $lfrom) $out .= $separator;

            if ($i<=$pos and $pos<($i+$limit))
                $out.='<span class="'.$class_numon.'">'.(floor($i/$limit)+1).'</span>';
            else
                $out.='<a href="'.htmlspecialchars(str_replace("<pager>", $i, $url), ENT_QUOTES).'" class="'.$class_num.'" '.$link_special_html.'>'.(floor($i/$limit)+1).'</a>';
        }
    }

 	if (($pos+$limit)<$items) 
		$out.=$separator.'<a href="'.htmlspecialchars(str_replace("<pager>", ($pos+$limit), $url), ENT_QUOTES).'" class="'.$class_text.'" '.$link_special_html.'>'.$txt_next.'</a>';
	elseif ($display=='always') $out.=$separator.'<span class="'.$class_text.'">'.$txt_next.'</span>';

	if ($txt_last){
	 	if (($pos+$limit)<$items) 
			$out.=$separator.'<a href="'.htmlspecialchars(str_replace("<pager>", (floor($items/$limit)*$limit), $url), ENT_QUOTES).'" class="'.$class_text.'" '.$link_special_html.'>'.$txt_last.'</a>';
		elseif ($display=='always') $out.=$separator.'<span class="'.$class_text.'">'.$txt_last.'</span>';
	}

	
	return $out;  
}



?>
