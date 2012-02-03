/**
 *	Various javascript functions used on most of pages
 *  $Id: functions.js,v 1.18 2009/12/17 12:11:56 kozlik Exp $
 */

/**
 *	Execute function in diferent scope
 */ 
Function.prototype.bindObj = function(object) {
	var __method = this;
	return function() {
		return __method.apply(object, arguments);
	}
} 

/**
 *	Trim methods for a string
 */ 
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}

String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}

String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}

/**
 *  Simplify inheritance
 *  
 *  @reference: http://phrogz.net/JS/Classes/OOPinJS2.html  
 */ 
Function.prototype.inheritsFrom = function( parentClassOrObject ){ 
	if ( parentClassOrObject.constructor == Function ) 
	{ 
		//Normal Inheritance 
		this.prototype = new parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject.prototype;
	} 
	else 
	{ 
		//Pure Virtual Inheritance 
		this.prototype = parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject;
	} 
	return this;
} 

function loadJS(url){
   var e = document.createElement("script");
   e.src = url;
   e.type="text/javascript";
   document.getElementsByTagName("head")[0].appendChild(e);
}

/* confirm click to <a href=""> */

function linkConfirmation(theLink, message){
    var is_confirmed = confirm(message);
    if (is_confirmed) {
        theLink.href;
    }
    return is_confirmed;
}


/**
 *	Send a synchronic http request 
 *	
 *	Send http request with method POST and 'post_data' in its body.
 *	If param 'post_data' is not present, method GET is used instead POST 
 *
 *  @param	string	url			URL of the request
 *  @param	string	post_data 	data sent in the request
 *  @return http_request		result of the requst
 */  
 
function ajax_sync_request(url, post_data){
	var http_request;

	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		http_request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // IE
		http_request = new ActiveXObject('Microsoft.XMLHTTP');
	} else return null;
	

	if (post_data){
		http_request.open('POST', url, false);
		http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http_request.send(post_data);
	}
	else{
		http_request.open('GET', url, false);
		http_request.send(null);
	}

	return http_request;
}

/**
 *	Send a asynchronic http request 
 *	
 *	Send http request with method POST and 'post_data' in its body.
 *	If param 'post_data' is not present, method GET is used instead POST 
 *
 *  @param	string		url			URL of the request
 *  @param	string		post_data 	data sent in the request
 *  @param	function	callback 	function called when httP request state change
 *  @return http_request			result of the requst
 */  
 
function ajax_async_request(url, post_data, callback){
	var http_request;

	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		http_request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // IE
		http_request = new ActiveXObject('Microsoft.XMLHTTP');
	} else return null;
	

	if (post_data){
		http_request.open('POST', url, true);
		http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http_request.onreadystatechange = function() { callback(http_request); };
 		http_request.send(post_data);
	}
	else{
		http_request.open('GET', url, true);
		http_request.onreadystatechange = function() { callback(http_request); };
		http_request.send(null);
	}

	return http_request;
}

/**
 *	Add new css class to html element
 * 
 *	@param	object	el  
 *	@param	string	className
 */
function addClassNameToEl(el, className){
	el.className += " " + className;
}

/**
 *	Remove the css class to html element
 * 
 *	@param	object	el  
 *	@param	string	className
 */
function remClassNameFromEl(el, className){
	var newClassName = "";
	var classNames = el.className.split(' ');
		
	for (var i=0; i<classNames.length; i++){
		if (classNames[i] != className) newClassName += " "+classNames[i];
	}

	el.className = newClassName;
}

/**
 *  Check whether html element has given class
 * 
 *	@param	object	el  
 *	@param	string	className
 */ 
function hasClassName(el, className) {
    return el.className && new RegExp("(^|\\s)" + className + "(\\s|$)").test(el.className);
}

/**
 *	Enable form element and remove CSS class "disabled" from it
 * 
 *	@param	object	el  
 */
function enableFormEl(el){
    el.disabled = false;
    remClassNameFromEl(el, "disabled");
}

/**
 *	Disable form element and set CSS class "disabled" to it
 * 
 *	@param	object	el  
 */
function disableFormEl(el){
    el.disabled = true;
    addClassNameToEl(el, "disabled");
}

/**
 *  toggle visibility of an element 
 */
function toggle_visibility(el){
	if (el.style.display=="none" || el.style.display==""){
		el.style.display = "block";
	}
	else{
		el.style.display = "none";
	}
}

/**
 *  Return the item of radio button with given value
 */ 
function get_radio_by_value(el, val){

    for (var i=0; i<el.length; i++){
        if (el.item(i).value == val){
            return el.item(i);
        }
    }
    return null;
}

/**
 *  Get element identified by its tag name and class name. Function return 
 *  only first matched element or null
 *      
 *  @param  Element parentEl     parrent element of the tree inside which the search is performed
 *  @param  string  tagName      name of tag to search
 *  @param  string  className    name of class
 *  @return Element 
 */ 
function get_element_by_className(parentEl, tagName, className){

    if (tagName == null)    tagName = '*';

    var elements = parentEl.getElementsByTagName(tagName);
    var classNames;
    

    for (var i=0; i<elements.length; i++){
        classNames = elements[i].className.split(' ');
        for (var j=0; j<classNames.length; j++){
            if (classNames[j] == className) return elements[i];
        }
    }

    return null;
}

/**
 *  Get array of elements identified by its tag name and class name
 *      
 *  @param  Element parentEl     parrent element of the tree inside which the search is performed
 *  @param  string  tagName      name of tag to search
 *  @param  string  className    name of class
 *  @return Array 
 */ 
function get_elements_by_className(parentEl, tagName, className){

    if (tagName == null)    tagName = '*';

    var classElements = new Array();
    var elements = parentEl.getElementsByTagName(tagName);
    var pattern = new RegExp("(^|\\s)"+className+"(\\s|$)");
    

    for (var i=0, j=0; i<elements.length; i++){
        if (pattern.test(elements[i].className)) classElements[j++] = elements[i];
    }

    return classElements;
}

/**
 *  Checks if a value exists in an array
 *  
 *  @param  mixed   value   The searched value
 *  @param  Array   ar      The array
 *  @return bool            Returns TRUE if value is found in the array, FALSE otherwise.     
 */
function in_array(value, ar){
	for (var i=0; i<ar.length; i++){
		if (ar[i] == value) return true;
	}
	return false;
}

/**
 *      Set selectedIndex of select by value of option
 */ 
function set_select_by_value(el, val){

    for (var i=0; i<el.options.length; i++){
        if (el.options[i].value == val){
            el.selectedIndex=i;
            return true;
        }
    }
    return false;
}

/**
 *      Enable/disable a link
 *      
 *  THis function expect the &lt;a&gt; element wrapped within &lt;span&gt; element. 
 *  Reference to the &lt;span&gt; should be parameter of the function. Function
 *  also expect the link is initialy enabled. 
 *      
 *  @param  bool                en      enable/disable
 *  @param  Element parentEl    parrent &lt;span&gt; element which wrap the &lt;a&gt; element
 */ 
function enable_link(en, parentEl){
    
    var linkEls = parentEl.getElementsByTagName('a');
    
    if (en){ //enable
        if (linkEls.length > 0) return; // links already enabled

        // restore content of the wraping <span> element
        parentEl.innerHTML = parentEl.linkUserData;
        // delete the stored data
        parentEl.linkUserData = null;
    }
    else{    //disable
        if (linkEls.length == 0) return; // no links enabled

        var disabledClass = linkEls[0].getAttribute('data-disClass');
        if (disabledClass == null) disabledClass="disabledLink";
        
        // save content of wraping <span> element
        parentEl.linkUserData = parentEl.innerHTML;

        // and replace content of the <span> element with another one
        parentEl.innerHTML = '<span class="'+disabledClass+'">'+linkEls[0].innerHTML+'</span>';
    }
}

/**
 *  This function is same as HTMLSpecialChars function from PHP
 */ 
function HTMLSpecialChars(str){
    str = str.replace("&", "&amp;", "g");
    str = str.replace("<", "&lt;", "g");
    str = str.replace(">", "&gt;", "g");
    str = str.replace("\"", "&quot;", "g");
    str = str.replace("'", "&#039;", "g");
    return str;
}

function HTMLSpecialCharsDecode(string, quote_style) {
    // Convert special HTML entities back to characters  
    // 
    // version: 1006.1915
    // discuss at: http://phpjs.org/functions/htmlspecialchars_decode    
    // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');    
    // *     returns 1: '<p>this -> &quot;</p>'
    // *     example 2: htmlspecialchars_decode("&amp;quot;");
    // *     returns 2: '&quot;'
    
    var optTemp = 0, i = 0, noquotes = false;
    
    if (typeof quote_style === 'undefined') {        
        quote_style = 2;
    }
    string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,        
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE' : 4    };
    
    if (quote_style === 0) {
        noquotes = true;
    }
    
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags        
        quote_style = [].concat(quote_style);
        for (i=0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;            
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP    
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    }
    // Put this in last place to avoid escape being double-decoded    
    string = string.replace(/&amp;/g, '&');
 
    return string;
}

/**
 *  Parse host part from sip uri
 *    
 *  @param  string  uri     sip uri
 *  @return string          hostpart or FALSE on invalid uri 
 */
function parse_host_from_sip_uri(uri){


    if      (uri.substr(0,4).toLowerCase() == 'sip:')  uri = uri.substr(4); //strip initial 'sip:'
    else if (uri.substr(0,5).toLowerCase() == 'sips:') uri = uri.substr(5); //strip initial 'sips:'
    else    return false; //not valid uri
    
    var ipv6 = 0;
    var hostpos = uri.indexOf('@');
    var hostlen = null;

    if ( hostpos < 0 ) hostpos = 0;
    else               hostpos++; 

    for (var i=hostpos; (i < uri.length) && (hostlen == null); i++){
        switch (uri.substr(i, 1)){
        case '[':  ipv6++; break;
        case ']':  ipv6--; break;
        case ':':
                   if (!ipv6){ //colon is not part of ipv6 address
                        hostlen = i-1;  //colon is separator of host and port
                        break;
                   } 
                   break;
        case ';':
                   hostlen = i-1;  //semicolon is start of uri parameters
                   break;
        }
    }

    if (hostlen == null) hostlen = uri.length;

    // hostlen now do not contain real lenght of host part, 
    // but the position of its end, so calculate the length:
    
    hostlen = hostlen - hostpos + 1;
    
    return uri.substr(hostpos, hostlen);
}


/**
 *  Parse port from sip uri
 *    
 *  @param  string  uri     sip uri
 *  @return int             port number or FALSE on invalid uri or NULL when no port in the uri  
 */
function parse_port_from_sip_uri(uri){

    if      (uri.substr(0,4).toLowerCase() == 'sip:')  uri = uri.substr(4); //strip initial 'sip:'
    else if (uri.substr(0,5).toLowerCase() == 'sips:') uri = uri.substr(5); //strip initial 'sips:'
    else    return false; //not valid uri
    
    var ipv6 = 0;
    var portpos = null;
    var ch;

    /* start parsing after '@' to avoid some special characters in user part */
    var startpos = uri.indexOf('@');
    if ( startpos < 0 ) startpos = 0;
    else                startpos++; 

    for (var i=startpos; (i < uri.length) && (portpos == null); i++){
        ch = uri.substr(i, 1);

        switch (ch){
        case '[':  ipv6++; break;
        case ']':  ipv6--; break;
        case ':':
                   if (!ipv6){ //colon is not part of ipv6 address
                        portpos = i;  //position of port inside address string
                        break;
                   } 
                   break;
        case ';':
                   return null;  //start of uri parameters -> no port in the uri
                   break;
        }
    }

    if (portpos == null) return null;   //no port in the uri

    portpos++; //move after the colon
    var portlen = 0;

    for (var i=portpos; i < uri.length; i++){
        ch = uri.substr(i, 1);

        if (ch<'0' || ch>'9') break;
        portlen++;
    }

    if (portlen == 0) return false; //no port in uri, but it contains colon -> invalid uri

    var port = Number(uri.substr(portpos, portlen));
    if (port == Number.NaN)     return false; //should never happen, but to be sure...
   
    return port;
}

/**
 *  Register handler (fn) of event (evt) on object (obj) - browser independent
 */
function add_event(obj, evt, fn){
    if (obj.addEventListener) //w3c model
        obj.addEventListener(evt, fn, false);
    else if (obj.attachEvent) //MS model
        obj.attachEvent('on'+evt, fn);
    else //other
        obj['on'+evt] = fn;
}

/**
 *  Unregister handler (fn) of event (evt) on object (obj) - browser independent
 */
function remove_event(obj, evt, fn){
	if (obj.removeEventListener) //w3c model
		obj.removeEventListener(evt, fn, false);
	else if (obj.detachEvent) //MS model
		obj.detachEvent('on'+evt, fn);
    else //other
        obj['on'+evt] = null;
}


/**
 *  Get absolute position of element
 */
function getAbsolutePosition( oElement ) {
    if( typeof( oElement.offsetParent ) != 'undefined' ) {
        for( var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent ) {
            posX += oElement.offsetLeft;
            posY += oElement.offsetTop;
    }
        return [ posX, posY ];
    } else {
        return [ oElement.x, oElement.y ];
    }
}

