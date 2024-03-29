<?php
/**
 * Miscellaneous functions and variable definitions
 *
 * @author    Karel Kozlik
 * @package   serweb
 */

/**
 *  Defining regular expressions
 *
 *  @package    serweb
 */
class Creg{

    protected $SP;
    protected $HTAB;
    protected $alphanum;
    protected $mark;
    protected $reserved;
    protected $unreserved;
    protected $escaped;
    protected $user_unreserved;
    protected $uric;
    protected $user;
    protected $utf8_cont;
    protected $utf8_nonascii;

    protected $port;
    protected $hex4;
    protected $hexseq;
    protected $hexpart;
    protected $ipv4address;
    protected $ipv6address;
    protected $ipv6reference;

    protected $toplabel;
    protected $domainlabel;
    protected $hostname;
    protected $host;
    protected $domainname;

    protected $token;
    protected $param_unreserved;
    protected $paramchar;
    protected $pname;
    protected $pvalue;
    protected $uri_parameter;
    protected $uri_parameters;
    protected $method;

    protected $transport_param;
    protected $user_param;
    protected $method_param;
    protected $ttl_param;
    protected $maddr_param;
    protected $lr_param;
    protected $other_param;

    protected $address;
    protected $sip_address;
    protected $sips_address;
    protected $sip_s_address;
    protected $srv_service;

    protected $visual_separator;
    protected $phonedigit;
    protected $phonedigit_hex;
    protected $local_number_digits;
    protected $global_number_digits;

    protected $phone_context;
    protected $isdn_subaddress_param;
    protected $extension_param;
    protected $context_param;
    protected $par;

    protected $local_number;
    protected $global_number;
    protected $telephone_subscriber;
    protected $tel_uri;

    protected $sip_header_name;
    protected $sip_header_value;
    protected $sip_header;
    protected $sip_header_js;

    protected $phonenumber;
    protected $phonenumber_strict;
    protected $email;
    protected $reason_phrase;
    protected $reason_phrase_ascii;
    protected $reason_phrase_js;
    protected $reason_phrase_ascii_js;

    protected $global_hex_digits;
    protected $rn_descriptor;
    protected $natural_num;

    public function __construct(){
        global $config, $reg_validate_email;

        $this->SP=" ";
        $this->HTAB="\t";
        $this->alphanum="[a-zA-Z0-9]";
        $this->mark="[-_.!~*'()]";
        $this->reserved="[;/?:@&=+$,]";
        $this->unreserved="(".$this->alphanum."|".$this->mark.")";
        $this->escaped="(%[0-9a-fA-F][0-9a-fA-F])";
        $this->user_unreserved="[&=+$,;?/]";
        $this->uric="(".$this->reserved."|".$this->unreserved."|".$this->escaped.")";
        $this->user="(".$this->unreserved."|".$this->escaped."|".$this->user_unreserved.")+";

        $this->port="[0-9]+";
        $this->hex4="([0-9a-fA-F]{1,4})";
        $this->hexseq="(".$this->hex4."(:".$this->hex4.")*)";
        $this->hexpart="(".$this->hexseq."|(".$this->hexseq."::".$this->hexseq."?)|(::".$this->hexseq."?))";
        $this->ipv4address="([0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3})";
        $this->ipv6address="(".$this->hexpart."(:".$this->ipv4address.")?)";
        $this->ipv6reference="(\\[".$this->ipv6address."])";

        $this->utf8_cont = "[\x80-\xbf]";
        $this->utf8_nonascii = "([\xc0-\xdf]".$this->utf8_cont.")|".
                               "([\xe0-\xef]".$this->utf8_cont."{2})|".
                               "([\xf0-\xf7]".$this->utf8_cont."{3})|".
                               "([\xf8-\xfb]".$this->utf8_cont."{4})|".
                               "([\xfc-\xfd]".$this->utf8_cont."{5})";

        /* toplabel is the name of top-level DNS domain ".com" -- alphanum only
           domainlabel is one part of the dot.dot name string in a DNS name ("iptel");
             it must begin with alphanum, can contain special characters (-) and end
             with alphanums
           hostname can include any number of domain lables and end with toplabel
        */
        $this->toplabel="([a-zA-Z]|([a-zA-Z](-|".$this->alphanum.")*".$this->alphanum."))";
        $this->domainlabel="(".$this->alphanum."|(".$this->alphanum."(-|".$this->alphanum.")*".$this->alphanum."))";
        $this->hostname="((".$this->domainlabel."\\.)*".$this->toplabel."\\.?)";
        $this->host="(".$this->hostname."|".$this->ipv4address."|".$this->ipv6reference.")";

        // domain name according to RFC 1035, section 2.3.1
        $this->domainname="((".$this->toplabel."\\.)*".$this->toplabel.")";

        // service name used in srv records
        $this->srv_service="_[sS][iI][pP]._(([uU][dD][pP])|([tT][cC][pP])|([sS][cC][tT][pP])).(".$this->domainname.")";


        $this->token="(([-.!%*_+`'~]|".$this->alphanum.")+)";
        $this->param_unreserved="\\[|]|[/:&+$]";
        $this->paramchar="(".$this->param_unreserved."|".$this->unreserved."|".$this->escaped.")";
        $this->pname="((".$this->paramchar.")+)";
        $this->pvalue="((".$this->paramchar.")+)";

        $this->method="((INVITE)|(ACK)|(OPTIONS)|(BYE)|(CANCEL)|(REGISTER)|".$this->token.")";

        $this->transport_param="(transport=((udp)|(tcp)|(sctp)|(tls)|".$this->token."))";
        $this->user_param="(user=((phone)|(ip)|".$this->token."))";
        $this->method_param="(method=".$this->method.")";
        $this->ttl_param="(ttl=[0-9]{1,3})";
        $this->maddr_param="(maddr=".$this->host.")";
        $this->lr_param="(lr)";
        $this->other_param="(".$this->pname."(=".$this->pvalue.")?)";

        $this->uri_parameter="(".$this->transport_param."|".$this->user_param."|".
                    $this->method_param."|".$this->ttl_param."|".$this->maddr_param."|".
                    $this->lr_param."|".$this->other_param.")";
        $this->uri_parameters="((;".$this->uri_parameter.")*)";

        $this->address="(".$this->user."@)?".$this->host."(:".$this->port.")?".$this->uri_parameters;

        /** Regex matching sip uri. See RFC 3261 chapter 25.1 */
        $this->sip_address="[sS][iI][pP]:".$this->address;
        /** Regex matching sips uri. See RFC 3261 chapter 25.1 */
        $this->sips_address="[sS][iI][pP][sS]:".$this->address;
        /** Regex matching sip or sips uri. See RFC 3261 chapter 25.1 */
        $this->sip_s_address="[sS][iI][pP][sS]?:".$this->address;



        $this->visual_separator = "[-.()]";
        $this->phonedigit = "[0-9]|".$this->visual_separator;
        $this->phonedigit_hex = "[0-9a-fA-F*#]|".$this->visual_separator;

        $this->local_number_digits = "((".$this->phonedigit_hex.")*[0-9a-fA-F*#])(".$this->phonedigit_hex.")*)";
        $this->global_number_digits = "(\\+(".$this->phonedigit.")*[0-9](".$this->phonedigit.")*)";

        $this->phone_context = $this->hostname."|".$this->global_number_digits;
        $this->isdn_subaddress_param = ";isub=(".$this->uric.")+";
        $this->extension_param = ";ext=(".$this->phonedigit.")+";
        $this->context_param = ";phone-context=".$this->phone_context;
        $this->par = "(;".$this->uri_parameter.")|(".$this->extension_param.")|(".$this->isdn_subaddress_param.")";


        $this->local_number = "(".$this->local_number_digits."(".$this->par.")*".$this->context_param."(".$this->par.")*)";
        $this->global_number = "(".$this->global_number_digits."(".$this->par.")*)";

        $this->telephone_subscriber = "(".$this->global_number."|".$this->local_number.")";

        /** tel URI according to RFC3966 */
        $this->tel_uri="[tT][eE][lL]:".$this->telephone_subscriber;


        /** reg.exp. validating sip header name */
        $this->sip_header_name  = "(\\[|]|\\\\|[-\"'!@#$%^&*()?*+,./;<>=_{}|~A-Za-z0-9])+";
        /** reg.exp. validating value of sip header */
        $this->sip_header_value = "(\\[|]|\\\\|[-\"'!@#$%^&*()?*+,./;<>=_{}|~A-Za-z0-9:])+";

        /** reg.exp. validating sip header name
         *  @deprec  this is some old not correct defintion replaced by $this->sip_header_name
         */
        $this->sip_header="([^][ ()<>@,;:\\\\=\"/?{}]+)";
        /** same regex, but for use in javascript
         *  @deprec  this is some old not correct defintion replaced by $this->sip_header_name
         */
        $this->sip_header_js="([^\\]\\[ ()<>@,;:\\\\=\"/?{}]+)";


        /** regex for phonenumber which could contain some characters as: - / <space> this characters should be removed */
        $this->phonenumber = $config->phonenumber_regex;        // "\\+?[-/ 1-9]+"

        /** strict phonenumber - only numbers and optional initial + */
        $this->phonenumber_strict = $config->strict_phonenumber_regex;      // "\\+?[1-9]+"

        $this->email = $reg_validate_email;

        /** regex matching reason phrase from status line */
        $this->reason_phrase = "(".$this->reserved."|".$this->unreserved."|".
                            $this->escaped."|".$this->utf8_nonascii."|".
                            $this->SP."|".$this->HTAB.")*";

        /** like reason_phrase, but matching only ascii chars */
        $this->reason_phrase_ascii = "(".$this->reserved."|".$this->unreserved."|".
                            $this->escaped."|".$this->SP."|".$this->HTAB.")*";

        /** Regex matching reason phrase from status line.
         *  This is javascript version of the above. This uses interval
         *  of unicode character codes instead of utf8_nonascii regexp.
         */
        $this->reason_phrase_js = "(".$this->reserved."|".$this->unreserved."|".
                            $this->escaped."|[\\u0080-\\uFFFF]|".
                            $this->SP."|".$this->HTAB.")*";

        /** like reason_phrase_js, but matching only ascii chars */
        $this->reason_phrase_ascii_js = "(".$this->reserved."|".$this->unreserved."|".
                            $this->escaped."|".$this->SP."|".$this->HTAB.")*";

        $this->global_hex_digits = "\\+[0-9]{1,3}(".$this->phonedigit_hex.")*";

        /** regex matching value of rn-context uri param by RFC4636 */
        $this->rn_descriptor = "(".$this->hostname.")|(".$this->global_hex_digits.")";


        /** regex matching natural number */
        $this->natural_num = "[0-9]+";
    }

    /**
     *  Attempts to return a reference to a Creg instance.
     *  Only creating a new instance if no Creg instance currently exists.
     *
     *  @return object Creg     instance of Creg class
     *  @static
     *  @access public
     */
    public static function &singleton(){
        static $instance;

        if(! isset($instance)) $instance = new Creg();

        return $instance;
    }

    /**
     * parse domain name from sip address
     *
     * @param string $sip sip address
     * @return string domain name
     */
    function get_domainname($sip){
        return preg_replace(pregize($this->sip_s_address), "\\5", $sip);
    }


    /** return javascript which do the same as method {@link get_domainname}
     *
     *  @param string $in_var   name of js variable with string containing sip uri
     *  @param string $out_var  name of js variable to which hostpart will be stored
     *  @return string          line of javascript code
     */
    function get_domainname_js($in_var, $out_var){
        return $out_var." = ".$in_var.".replace(/".str_replace('/','\/',$this->sip_s_address)."/, '\$5')";
    }

    /**
     * parse user name from sip address
     *
     * @param string $sip sip address
     * @return string username
     */
    function get_username($sip){

        $uname=preg_replace(pregize($this->sip_s_address), "\\1", $sip);

        //remove the '@' at the end
        return substr($uname,0,-1);
    }

    /**
     * parse port number from sip address
     *
     * @param string $sip sip address
     * @return string port
     */
    function get_port($sip){
        preg_match(pregize($this->sip_s_address), $sip, $regs);

        if (!empty($regs[38])){
            //remove the ':' at the begining
            return substr($regs[38], 1);
        }

        return false;
    }

    /**
     * return regular expression for validate user part of uri
     *
     * @return string
     */
    public function get_user_regex() : string{
        return $this->user;
    }

    /**
     * return regular expression for validate hostname
     *
     * @return string
     */
    public function get_hostname_regex() : string{
        return $this->hostname;
    }

    public function get_ipv4address_regex() : string{
        return $this->ipv4address;
    }

    public function get_ipv6address_regex() : string{
        return $this->ipv6address;
    }

    public function get_param_name_regex() : string{
        return $this->pname;
    }

    public function get_param_value_regex() : string{
        return $this->pvalue;
    }

    public function get_sip_header_name_regex() : string{
        return $this->sip_header_name;
    }

    public function get_sip_header_value_regex() : string{
        return $this->sip_header_value;
    }

    /**
     *  Parse parameters from sip uri
     *
     *  @param string $sip  sip uri
     *  @return array       associative array of parameters and their values
     */
    function get_parameters($sip){
        $params = explode(';', $sip);
        //first element is containing part of sip uri before parameters
        unset($params[0]);

        $out = array();
        if (is_array($params)){
            foreach($params as $param){
                $p = explode('=', $param, 2);
                $out[$p[0]] = $p[1];
            }
        }

        return $out;
    }

    /** converts string which can be accepted by regex $this->phonenumber to string which can be accepted by regex $this->phonenumber_strict
     *
     *  @param string $phonenumber
     *  @return string
     */
    function convert_phonenumber_to_strict($phonenumber){
        return str_replace(array('-', '/', ' ', '(', ')'), "", $phonenumber);
    }

    /** return javascript which do the same as method {@link convert_phonenumber_to_strict}
     *
     *  @param string $in_var   name of js variable with string for conversion
     *  @param string $out_var  name of js variable to which converted string will be stored
     *  @return string          line of javascript code
     */
    function convert_phonenumber_to_strict_js($in_var, $out_var){
        return $out_var." = ".$in_var.".replace(/[-\\/ ()]/g, '')";
    }

    /**
     *  check if given string is in format of IPv4 address
     *
     *  @param  string  $adr    IPv4 address
     *  @return bool
     */
    function is_ipv4address($adr){
        if (preg_match(pregize("^".$this->ipv4address."$"), $adr)) return true;
        else return false;
    }

    /**
     *  check if all parts of given IPv4 address are in range 0-255
     *
     *  @param  string  $adr    IPv4 address
     *  @return bool
     */
    function ipv4address_check_part_range($adr){
        // check if given string is IPv4 address
        if (!$this->is_ipv4address($adr)) return false;

        $parts = explode(".", $adr);

        foreach ($parts as $v){
            if (!is_numeric($v)) return false;      // part is not numeric

            $v = (int)$v;
            if ($v<0 or $v>255) return false;       // wrong range
        }

        return true;
    }


    /**
     *  Return javascript function checking range of parts of IPV4 address in SIP URI
     *
     *  @param  string  $name   name of javascript function which will be generated
     *  @return string
     */
    function ipv4address_check_part_range_js_fn($name){
        $js = "
            function ".$name."(adr){

                // parse host part from SIP uri
                var re = /".str_replace('/','\/',$this->sip_s_address)."/;
                var hostname = adr.replace(re, '\$5');

                //check if host part is in format of IPv4 address
                var re = /".str_replace('/','\/',"^".$this->ipv4address."$")."/;
                if (re.test(hostname)){

                    // split address to parts
                    var ipv4_parts = hostname.split('.');

                    for (var i=0; i < ipv4_parts.length; i++){
                        var int_part = Number(ipv4_parts[i]);
                        if (int_part == Number.NaN){
                            return false;           // part is not numeric
                        }

                        if (int_part < 0 || int_part > 255){
                            return false;           // wrong range
                        }
                    }
                }
                return true;
            }
        ";
        return $js;
    }

    /**
     *  Check range of TCP/UDP port
     *
     *  @param  string  $port
     *  @return bool
     */
    function port_check_range($port){
        if (!is_numeric($port)) return false;

        $port = (int)$port;
        if ($port < 1 or $port > 65535) return false;

        return true;
    }

    /**
     *  Return javascript function checking range of TCP/UDP port inside SIP uri
     *
     *  @param  string  $name   name of javascript function which will be generated
     *  @return string
     */
    function port_check_range_js_fn($name){
        $js = "
            function ".$name."(adr){

                /* parse port from sip uri */

                if      (adr.substr(0,4).toLowerCase() == 'sip:')  adr = adr.substr(4); //strip initial 'sip:'
                else if (adr.substr(0,5).toLowerCase() == 'sips:') adr = adr.substr(5); //strip initial 'sips:'
                else    return false; //not valid uri


                var ipv6 = 0;
                var portpos = null;
                var ch;

                for (var i=0; (i < adr.length) && (portpos == null); i++){
                    ch = adr.substr(i, 1);

                    switch (ch){
                    case '[':  ipv6++; break;
                    case ']':  ipv6--; break;
                    case ':':
                               if (!ipv6){ //semicolon is not part of ipv6 address
                                    portpos = i;  //position of port inside address string
                                    break;
                               }
                    }
                }

                if (portpos == null) return true;   //no port in the uri

                portpos++; //move after the semicolon
                var portlen = 0;

                for (var i=portpos; i < adr.length; i++){
                    ch = adr.substr(i, 1);

                    if (ch<'0' || ch>'9') break;
                    portlen++;
                }

                if (portlen == 0) return false; //no port in uri, but it contains semicolon

                var port = Number(adr.substr(portpos, portlen));
                if (port == Number.NaN)     return false; //should never happen, but to be sure...

                if (port < 1 || port > 65535){
                            return false;   //invalid port range
                }

                return true;
            }
        ";
        return $js;
    }

    /**
     *  Check given netmask and return format it is written in.
     *
     *  @return string  "bitcount", "decimal", "hexadecimal" or FALSE if netmask match no format
     */
    function get_netmask_format($netmask){
        if (is_numeric($netmask)) return "bitcount";
        if ($this->is_ipv4address($netmask)) return "decimal";

        // I am not sure about this regexp, can't find correct RFC now.
        if (preg_match("/0x[0-9a-fA-F]{8}/", $netmask)) return "hexadecimal";

        return false;
    }

    /**
     *  check if given IPv4 address is valid netmask
     *
     *  @param  string  $adr    IPv4 address
     *  @param  array   $format allowed formats of netmask. It can contain following values:
     *                          "bitcount", "decimal", "hexadecimal"
     *  @return bool
     */
    function check_netmask($adr, $format = array("bitcount", "decimal", "hexadecimal")){

        if (in_array("bitcount", $format)){
            if (is_numeric($adr) and (int)$adr >=0 and (int)$adr <= 32) return true;
        }

        // if decimal format allowed and the given string looks like IPv4 address
        if (in_array("decimal", $format) and $this->is_ipv4address($adr)){

            $parts = explode(".", $adr);

            $starting = true;
            foreach ($parts as $v){
                if (!is_numeric($v)) return false;      // part is not numeric

                $v = (int)$v;
                if ($starting){
                    /* allow ones at the begining */
                    if ($v == 255) continue;
                    if (!in_array($v, array(254, 252, 248, 240, 224, 192, 128, 0))){
                        return false;
                    }
                    $starting = false;
                }
                /* allow only zeros at the end */
                elseif ($v != 0) return false;
            }

            return true;
        }

        if (in_array("hexadecimal", $format) and substr($adr, 0, 2) == '0x'){
            $starting = true;
            $hex = strtolower(substr($adr, 2));

            for($i=0; $i<strlen($hex); $i++){
                if ($starting){
                    /* allow ones at the begining */
                    if ($hex[$i] == 'f') continue;
                    if (!in_array($hex[$i], array('e', 'c', '8', '0'))){
                        return false;
                    }
                    $starting = false;
                }
                /* allow only zeros at the end */
                elseif ($hex[$i] != '0') return false;
            }

            return true;
        }

        return false;
    }


    /**
     *  check if given IPv4 address is network address of network with given netmask
     *
     *  @param  string  $ip         network address (IPv4)
     *  @param  string  $netmask    netmask
     *  @param  array   $mask_format allowed formats of netmask. It can contain following values:
     *                          "bitcount", "decimal", "hexadecimal"
     *  @return bool
     */
    function check_network_address($ip, $netmask, $mask_format = array("bitcount", "decimal", "hexadecimal")){
        // check if given string is IPv4 address
        if (!$this->is_ipv4address($ip)) return false;
        if (!$this->check_netmask($netmask, $mask_format)) return false;

        $mask_format = $this->get_netmask_format($netmask);
        if (!$mask_format) return false;

        if ($mask_format == "decimal"){
            $parts_ip = explode(".", $ip);
            $parts_mask = explode(".", $netmask);

            for ($i=0; $i<4; $i++){
                $ip = (int)$parts_ip[$i];
                $mask = (int)$parts_mask[$i];
                if ($ip != ($ip & $mask)) return false;
            }
        }

        if ($mask_format == "hexadecimal"){
            $parts_ip = explode(".", $ip);
            $parts_mask = str_split(substr($netmask, 2), 2);

            for ($i=0; $i<4; $i++){
                $ip = (int)$parts_ip[$i];
                $mask = hexdec($parts_mask[$i]);
                if ($ip != ($ip & $mask)) return false;
            }
        }

        if ($mask_format == "bitcount"){
            $parts_ip = explode(".", $ip);
            $mask_table = array(0, 128, 192, 224, 240, 248, 252, 254, 255);

            for ($i=0; $i<4; $i++){
                $ip = (int)$parts_ip[$i];
                $part_mask = (int)$netmask;
                if ($part_mask > 8) $part_mask = 8;
                $netmask = $netmask - $part_mask;

                // convert bitcount to the mask
                $part_mask = $mask_table[$part_mask];
                if ($ip != ($ip & $part_mask)) return false;
            }
        }

        return true;
    }

    /**
     *  validate regular expression
     *
     *  Validate syntax of regular expression (uses PCRE - Perl Compatible
     *  Regular Expressions)
     *
     *  @param  string  $pattern    RegEx String pattern to validate
     *  @param  string  $format     pcre/posix
     *  @return bool                true if valid regex, false if not
     *
     */
    function check_regexp($pattern, $format = "pcre"){
        global $config;

        switch ($format){
        case "pcre":
            if ($config->external_regexp_validator_pcre){
                exec($config->external_regexp_validator_pcre." ".escapeshellarg($pattern), $output);

                if (isset($output[0]) and $output[0]=="1"){
                    return true;
                }
                return false;
            }

            /*
               external validator is not set
               some php validation should be implemented there
             */

            break;

        case "posix":
            if ($config->external_regexp_validator_posix){
                exec($config->external_regexp_validator_posix." ".escapeshellarg($pattern), $output);

                if (isset($output[0]) and $output[0]=="1"){
                    return true;
                }
                return false;
            }

            /*
               external validator is not set
               some php validation should be implemented there
             */

            break;
        default:
            die(__FILE__.":".__LINE__." check_regexp: unknown value of format attribute: '".$format."'");
        }

        return true;
    }

    /**
     *  check if given SIP address is valid
     *
     *  @param  string  $sip_addr
     *  @return bool
     */
    function check_sip_address($sip_addr){

        if (!preg_match(pregize("^".$this->sip_s_address."$"), $sip_addr)){
            return false;
        }
        else{
            $uri_domain = $this->get_domainname($sip_addr);
            $uri_port = $this->get_port($sip_addr);

            if ( ($this->is_ipv4address($uri_domain) and
                  !$this->ipv4address_check_part_range($uri_domain)) or

                 (false !== $uri_port and
                  !$this->port_check_range($uri_port))){

                return false;
            }
        }

        return true;
    }

    /**
     *  check if given IPv4 address is valid
     *
     *  @param  string  $ip_addr
     *  @return bool
     */
    function check_ipv4_address($ip_addr){

        if (!preg_match(pregize("^".$this->ipv4address."$"), $ip_addr)){
            return false;
        }
        else{
            if ( $this->is_ipv4address($ip_addr) and
                !$this->ipv4address_check_part_range($ip_addr)){

                return false;
            }
        }

        return true;
    }

    /**
     *  check if given IPv4 address and netmask is valid
     *
     *  @param  string  $ip_addr
     *  @param  array   $mask_format allowed formats of netmask. It can contain following values:
     *                          "bitcount", "decimal", "hexadecimal"
     *  @return bool
     */
    function check_ipv4_addr_netmask($ip_addr, $mask_format = array("bitcount", "decimal", "hexadecimal")){

        $value_a = explode("/", $ip_addr, 2);

        if (!isset($value_a[1])) $value_a[1] = "";
        if (!preg_match(pregize("^".$this->ipv4address."$"), $value_a[0])){
            return false;
        }
        else{
            if ( $this->is_ipv4address($value_a[0]) and
                !$this->ipv4address_check_part_range($value_a[0])){
                return false;
            }
            elseif (!$this->check_netmask($value_a[1], $mask_format)){
                return false;
            }
        }

        return true;
    }


    /**
     *  check if given IPv6 address is valid
     *
     *  Check if it is in form 1, 2 or 3 accroding RFC4291 section 2.2
     *  This function is slightly modified function from:
     *  http://crisp.tweakblogs.net/blog/3049/ipv6-validation-more-caveats.html
     *
     *  @param  string  $ip_addr
     *  @return bool
     */
    function check_ipv6_address($ip_addr){

        // fast exit for localhost
        if (strlen($ip_addr) < 3) return $ip_addr == '::';

        // Check if part is in IPv4 format
        if (strpos($ip_addr, '.')){
            $lastcolon = strrpos($ip_addr, ':');
            if (!($lastcolon && $this->check_ipv4_address(substr($ip_addr, $lastcolon + 1))))
                return false;

            // replace IPv4 part with dummy
            $ip_addr = substr($ip_addr, 0, $lastcolon) . ':0:0';
        }

        // check uncompressed
        if (strpos($ip_addr, '::') === false){
            return preg_match('/\A(?:'.$this->hex4.':){7}'.$this->hex4.'\z/i', $ip_addr);
        }

        // check colon-count for compressed format
        $colonCount = substr_count($ip_addr, ':');
        if ($colonCount < 8){
            return preg_match('/\A(?::|(?:'.$this->hex4.':)+):(?:(?:'.$this->hex4.':)*'.$this->hex4.')?\z/i', $ip_addr);
        }

        // special case with ending or starting double colon
        if ($colonCount == 8){
            return preg_match('/\A(?:::)?(?:'.$this->hex4.':){6}'.$this->hex4.'(?:::)?\z/i', $ip_addr);
        }

        return false;
    }

    /**
     *  check if given IP (v4 or v6) address and netmask is valid
     *
     *  @param  string  $ip_addr
     *  @param  array   $mask_format allowed formats of netmask (for v4 only). It can contain following values:
     *                          "bitcount", "decimal", "hexadecimal"
     *  @return bool
     */
    function check_ip_addr_netmask($ip_addr, $mask_format = array("bitcount", "decimal", "hexadecimal")){

        $value_a = explode("/", $ip_addr, 2);

        if (!isset($value_a[1])) $value_a[1] = "";
        // if the given address is IPv6 validate the given netmask is in range 0-128
        if ($this->check_ipv6_address($value_a[0])){
            if (is_numeric($value_a[1]) and (int)$value_a[1] >=0 and (int)$value_a[1] <= 128) return true;
            else return false;
        }
        // in other case check if it is a  valid IPv4 addr+netmask
        else return $this->check_ipv4_addr_netmask($ip_addr, $mask_format);
    }

    /**
     *  check if given IPv4 address and port is valid
     *
     *  @param  string  $value
     *  @return bool
     */
    function check_ipv4_addr_port($value){

        if (!preg_match(pregize("^".$this->ipv4address.":".$this->port."$"), $value)){
            return false;
        }
        else{
            $uri_domain = $this->get_domainname("sip:".$value);
            $uri_port = $this->get_port("sip:".$value);
            if ( ($this->is_ipv4address($uri_domain) and
                  !$this->ipv4address_check_part_range($uri_domain)) or

                 (false !== $uri_port and
                  !$this->port_check_range($uri_port))){

                return false;
            }
        }

        return true;
    }

    /**
     *  check if given hostname
     *
     *  @param  string  $value
     *  @return bool
     */
    function check_hostname($value){

        if (!preg_match(pregize("^".$this->host."$"), $value)){
            return false;
        }
        else{
            if ( $this->is_ipv4address($value) and
                !$this->ipv4address_check_part_range($value)){
                return false;
            }
        }

        return true;
    }

    /**
     *  Check whether the argument is natural number (as integer or it's
     *  string representation). Natural number is non-negative integer.
     */
    function is_natural_num($val){
        if (preg_match(pregize("^".$this->natural_num."$"), $val)) return true;
        return false;
    }
}

/**
 *  Quote delimiter characters in pcre regular expression
 *
 *  Takes str and puts a backslash in front of every character that is used as
 *  delimiter of the pcre regular expression. This is useful if you have
 *  a regular expression that you need to use in preg* function and it contain
 *  delimiter characters inside.
 *
 *  @param  string  $str
 *  @param  string  $delim  The delimiter used by in pcre reg.expIt is "/" by default
 *  @return string
 */
function preg_quote_delim($str, $delim="/"){
    return str_replace($delim, "\\".$delim, $str);
}

/**
 *  Add delimiters to regular expression so it can be used in preg* functions
 *
 *  @param  string  $regexp
 *  @return string
 */
function pregize($regexp){
    return "/".preg_quote_delim($regexp)."/";
}


/**
 * send email
 *
 * same as PHP function mail(), but additionaly is set header "From" by
 * config option {@link $config->mail_header_from}
 *
 * @param string $to address of recipient
 * @param string $text email body
 * @param array $headers email headers (associative array)
 * @return boolean TRUE if the mail was successfully accepted for delivery, FALSE otherwise
 *
 * @todo add error logging/reporting
 */

function send_mail($to, $text, $headers = array()){
    global $config;

    /* if subject isn't defined */
    if (!isset($headers['subject'])) $headers['subject'] = "";

    /* add from header */
    if (!isset($headers['from'])) $headers['from'] = $config->mail_header_from;

    /* convert associative array to string */
    $str_headers="";
    foreach ($headers as $k=>$v){
        /* exclude header 'subject'. It is given throught another parameter of function */
        if ($k=='subject') continue;
        $str_headers .= ucfirst($k).": ".$v."\n";
    }

    /* get charset */
    $charset = null;
    if (isset($headers['content-type']) and
        preg_match("/charset=([-a-z0-9]+)/i", $headers['content-type'], $regs)){

        $charset = $regs[1];
    }

    if (!function_exists('imap_8bit')){
        ErrorHandler::log_errors(PEAR::raiseError("Can not send mail. IMAP extension for PHP is not installed."));
        return false;
    }

    /* add information about charset to the header */
    if ($charset)
        $headers['subject'] = "=?".$charset."?Q?".imap_8bit($headers['subject'])."?=";

    /* enable tracking errors */
    ini_set('track_errors', 1);

    /* send email */
    @$a= mail($to, $headers['subject'], $text, $str_headers);

    /* if there was error during sending mail and error message is present, log the error */
    if (!$a and !empty($php_errormsg)){
        ErrorHandler::log_errors(PEAR::raiseError(html_entity_decode($php_errormsg)));
    }

    return $a;
}


/**
 *  Write to serweb log if logging is enabled
 *
 *  @param mixed $message       String or object containing the message to log.
 *  @param mixed $priority      The priority of the message. Valid values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING, PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
 *  @param array $opts          Allowed options:
 *                               - 'file' filename of the log message origin
 *                               - 'line' linenumber of the log message origin
 *  @return boolean             True on success or false on failure
 */

function sw_log($message, $priority = null, $opts=[]){
    global $serwebLog, $config;

    //if custom log function is defined, use it for log errors
    if (!empty($config->custom_log_function)){
        if (!isset($opts['file']) || !isset($opts['line'])){
            $db = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $opts['file'] = $db[0]['file'];
            $opts['line'] = $db[0]['line'];
        }

        return call_user_func($config->custom_log_function, $priority, $message, $opts['file'], $opts['line']);
    }
    elseif ($serwebLog){
        if (!is_string($message)) $message = json_encode($message);

        return $serwebLog->log($message, $priority);
    }

    return true;
}

/**
 * Write an exceptio into serweb log
 *
 * @param Throwable $e
 * @param string $priority      Check sw_log() function for details.
 */
function sw_log_exception(Throwable $e, $priority = PEAR_LOG_CRIT){

    $prev = $e->getPrevious();
    if ($prev) sw_log_exception($prev, $priority);

    $log_message = "Unhandled exception '".get_class($e)."' ";
    $log_message .= "with message: '".$e->getMessage()."' ";
    $log_message .= "in ".$e->getFile().":".$e->getLine()."\n";
    $log_message .= "Stack trace: \n";
    $log_message .= $e->getTraceAsString();

    return sw_log($log_message, $priority, ['file' => $e->getFile(), 'line' => $e->getLine()]);
}

/**
 *  Log action of user
 *
 *  Allowed options:
 *   - cancel (bool)  - indicates that submit of html form has been canceled [default: false]
 *   - errors (mixed) - string or array of errors which occurs during action [default: none]
 *
 *
 *  @param string $screen_name  Name of screen where the action has been performed.
 *  @param array $action        Action which has been performed.
 *  @param string $msg          Message describing the action
 *  @param bool $success        Has been action preformed successfully?
 *  @param array $opt           Optional parrameters - reserved for future use
 *  @return none
 */

function action_log($screen_name, $action, $msg=null, $success = true, $opt = array()){
    global $config;

    $eh = ErrorHandler::singleton();

    $opt['action_str'] = is_array($action) ? $action['action'] : $action;
    $opt['errors'] = $eh->get_errors_array();

    if (!empty($config->custom_act_log_function)){
        call_user_func($config->custom_act_log_function, $screen_name, $action, $msg, $success, $opt);
    }
    else{
        if (is_null($msg)) $msg = "action performed";
        sw_log($screen_name." - ".$opt['action_str']." ".$msg." ".($success ? "[successfull]" : "[failed]"), PEAR_LOG_INFO);
    }
}
/**
 *  get error message from PEAR_Error object and write it to $errors array and to error log
 *
 *  @param object $err_object PEAR_Error object
 *  @param array $errors array of error messages
 */

function log_errors($err_object, &$errors){
    global $serwebLog, $config;

    //get name of function from which log_errors is called
    $backtrace=debug_backtrace();
    if (isset($backtrace[1]['function'])) {
        if (isset($backtrace[1]['class']) and   //if this function is called from errorhandler class
            $backtrace[1]['function'] == 'log_errors' and
            $backtrace[1]['class'] == 'errorhandler'){

            if (isset($backtrace[2]['function'])) {
                $funct=$backtrace[2]['function'];
            }
            else $funct=null;
        }
        else{
            $funct=$backtrace[1]['function'];
        }
    }
    else $funct=null;


    //get backtrace frame from err_object which correspond to function from which log_errors is called
    $backtrace=$err_object->getBacktrace();
    $last_frame=end($backtrace);

    if ($funct and $funct!=__FUNCTION__){
        do{
            if ($last_frame['function']==$funct){
                $last_frame=prev($backtrace);
                break;
            }
        }while($last_frame=prev($backtrace));

        //if matchng frame is not found, use last_frame
        if (!$last_frame) {
            //if logging is enabled
            if ($serwebLog){
                $serwebLog->log("function: LOG ERRORS - bad parametr ".$funct, PEAR_LOG_ERR);
            }

            $last_frame=end($backtrace);
        }
    }

    $err_message = $err_object->getMessage();
    if ($config->log_error_return_location_of_error_to_html){
        $err_message .= ", file: ".$last_frame['file'].":".$last_frame['line'];
    }
    $errors[] = $err_message;

    $userInfo = "";
    if (method_exists($err_object, 'getUserInfo')) $userInfo = $err_object->getUserInfo();
    $log_message = $err_object->getMessage()." - ".$userInfo;

    //if custom log function is defined, use it for log errors
    if (!empty($config->custom_log_function)){
        call_user_func($config->custom_log_function, PEAR_LOG_ERR, $log_message, $last_frame['file'], $last_frame['line']);
    }

    //otherwise if logging is enabled, use default log function
    elseif ($serwebLog){

        $log_message= "file: ".$last_frame['file'].":".$last_frame['line'].": ".$log_message;
        //remove endlines from the log message
        $log_message=str_replace(array("\n", "\r"), "", $log_message);
        $log_message=preg_replace("/[[:space:]]{2,}/", " ", $log_message);
        $serwebLog->log($log_message, PEAR_LOG_ERR);

    }

}


/**
 *  Convert string to CSV format
 *
 *  @param string $str string to convert
 *  @param string $delim delimiter
 *  @return string
 */

function toCSV($str, $delim=','){
    $str = str_replace('"', '""', $str);    //double alll quotes
    $pos1 = strpos($str, '"');              // if $str contains quote or delim, quote it
    $pos2 = strpos($str, $delim);
    if (!($pos1===false and $pos2===false)) $str = '"'.$str.'"';
    return $str;
}

/**
 *  Return true if module $mod_name is loaded
 *
 *  @param string $mod_name     name of module
 *  @return bool
 */
function isModuleLoaded($mod_name){
    global $config;

    if (isset($config->modules[$mod_name]) and $config->modules[$mod_name])
        return true;
    else
        return false;
}

/**
 *  Return array of loaded modules
 *
 *  @return array
 */
function getLoadedModules(){
    global $config;

    if (isset($config->modules))
        return array_keys($config->modules, true);
    else
        return array();
}


/**
 *  This function return version 4 UUID by RFC4122, which is generating UUIDs
 *  from truly-random numbers.
 *
 *  @return string
 */
function rfc4122_uuid(){
   // version 4 UUID

    if (is_callable('random_bytes')){
        // works with PHP >= 7
        return implode('-', [
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(6))
        ]);
    }

   return sprintf(
       '%08x-%04x-%04x-%02x%02x-%012x',
       mt_rand(),
       mt_rand(0, 65535),
       bindec(substr_replace(
           sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
       ),
       bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
       mt_rand(0, 255),
       mt_rand()
   );
}

/**
 *  This function creates the specified directory using mkdir().  Note
 *  that the recursive feature on mkdir() is added in PHP5 so I need
 *  to create it myself for PHP4
 *
 *  @param string $path
 *  @param int    $mode     The mode is 0777 by default, which means the widest possible access. For more information on modes, read the details on the chmod() page.
 */
function RecursiveMkdir($path, $mode=0777){

    if (!file_exists($path)){
        // The directory doesn't exist.  Recurse, passing in the parent
        // directory so that it gets created.
        RecursiveMkdir(dirname($path));

        if (!mkdir($path, $mode)) trigger_error("Failed to create directory: '$path'", E_USER_WARNING);
    }
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        if ($dest !== "$source/$entry") {
            copyr("$source/$entry", "$dest/$entry");
        }
    }

    // Clean up
    $dir->close();
    return true;
}

/**
 * rm() -- Vigorously erase files and directories.
 *
 * @param $fileglob mixed If string, must be a file name (foo.txt), glob pattern (*.txt), or directory name.
 *                        If array, must be an array of file names, glob patterns, or directories.
 */
function rm($fileglob)
{
   if (is_string($fileglob)) {
       if (is_link($fileglob) or is_file($fileglob)) {
           return unlink($fileglob);
       } else if (is_dir($fileglob)) {
           $ok = rm("$fileglob/*");
           if (! $ok) {
               return false;
           }
           return rmdir($fileglob);
       } else {
           $matching = glob($fileglob);
           if ($matching === false) {
//               trigger_error(sprintf('No files match supplied glob %s', $fileglob), E_USER_WARNING);
//               return false;
                return true; //nothing to delete
           }
           $rcs = array_map('rm', $matching);
           if (in_array(false, $rcs)) {
               return false;
           }
       }
   } else if (is_array($fileglob)) {
       $rcs = array_map('rm', $fileglob);
       if (in_array(false, $rcs)) {
           return false;
       }
   } else {
       trigger_error('Param #1 must be filename or glob pattern, or array of filenames or glob patterns', E_USER_ERROR);
       return false;
   }
   return true;
}

/**
 * wrapper for function JSON_encode
 *
 * If function JSON_encode call it. In other case call function sw_JSON_encode
 *
 * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
 *                           if var is a strng, note that sw_JSON_encode() always expects it
 *                           to be in ASCII or UTF-8 format!
 *
 * @return   mixed   JSON string representation of input var or FALSE if a problem occurs
 * @access   public
 */
function my_JSON_encode($var){
    if (function_exists("JSON_encode")){
        return JSON_encode($var);
    }
    else {
        return sw_JSON_encode($var);
    }
}

/**
 * encodes an arbitrary variable into JSON format
 *
 * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
 *                           if var is a strng, note that sw_JSON_encode() always expects it
 *                           to be in ASCII or UTF-8 format!
 *
 * @return   mixed   JSON string representation of input var or FALSE if a problem occurs
 * @access   public
 */
function sw_JSON_encode($var){

    switch (gettype($var)) {
        case 'boolean':
            return $var ? 'true' : 'false';

        case 'NULL':
            return 'null';

        case 'integer':
            return (int) $var;

        case 'double':
        case 'float':
            return (float) $var;

        case 'string':
            // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT

            return '"'.js_escape($var).'"';

        case 'array':
           /*
            * As per JSON spec if any array key is not an integer
            * we must treat the the whole array as an object. We
            * also try to catch a sparsely populated associative
            * array with numeric keys here because some JS engines
            * will create an array with empty indexes up to
            * max_index which can cause memory issues and because
            * the keys, which may be relevant, will be remapped
            * otherwise.
            *
            * As per the ECMA and JSON specification an object may
            * have any string as a property. Unfortunately due to
            * a hole in the ECMA specification if the key is a
            * ECMA reserved word or starts with a digit the
            * parameter is only accessible using ECMAScript's
            * bracket notation.
            */

            // treat as a JSON object
            if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                $properties  = array();
                foreach($var as $k => $v){
                    $en_val = sw_JSON_encode($v);
                    if (false === $en_val) return false;
                    $properties[] = sw_JSON_encode(strval($k)).':'.$en_val;
                }

                return '{' . implode(',', $properties) . '}';
            }

            // treat it like a regular array
            $elements = array_map('sw_JSON_encode', $var);

            foreach($elements as $k => $v){
                if (false === $v) return false;
            }

            return '[' . implode(',', $elements) . ']';

        case 'object':
            $vars = get_object_vars($var);

            $properties  = array();
            foreach($vars as $k => $v){
                $en_val = sw_JSON_encode($v);
                if (false === $en_val) return false;
                $properties[] = sw_JSON_encode(strval($k)).':'.$en_val;
            }

            return '{' . implode(',', $properties) . '}';

        default:
            return false;
    }
}

/**
 *  Redirect client to secure connection and stop executing of the script
 */
function redirect_to_HTTPS(){
    /* if useing secure connection return true */
    if (!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS']!='off') return true;

    /* there is something wrong, we already tryed do redirect but it seems
       non secure connection is still used */
    if (isset($_GET['redirected_to_https'])) return false;


    /* do redirect to secure connection */
    $server_name = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
    $separator = (false === strstr($_SERVER['REQUEST_URI'], '?')) ? '?' : '&';

    /* for developer purpose - if need to use diferent port for redirect */
    if (isset($_COOKIE['_server_port'])) {
        /* if server name conatin port number */
        if (strpos($server_name, ":")) {
            /* strip the port from server name */
            $server_name = substr($server_name, 0, strpos($server_name, ":"));
        }
        /* and add port for https */
        $server_name .= ":".$_COOKIE['_server_port'];
    }

    Header("Location: https://".$server_name.$_SERVER['REQUEST_URI'].$separator."redirected_to_https=1");
    exit (0);
}

/**
 *  Redirect client to unsecure connection and stop executing of the script
 */
function redirect_to_HTTP(){
    /* if useing unsecure connection return true */
    if (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS']=='off') return true;

    /* there is something wrong, we already tryed do redirect but it seems
       secure connection is still used */
    if (isset($_GET['redirected_to_http'])) return false;


    /* do redirect to unsecure connection */
    $server_name = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
    $separator = (false === strstr($_SERVER['REQUEST_URI'], '?')) ? '?' : '&';

    /* for developer purpose - if need to use diferent port for redirect */
    if (isset($_COOKIE['_unsec_server_port'])) {
        /* if server name conatin port number */
        if (strpos($server_name, ":")) {
            /* strip the port from server name */
            $server_name = substr($server_name, 0, strpos($server_name, ":"));
        }
        /* and add port for https */
        $server_name .= ":".$_COOKIE['_unsec_server_port'];
    }

    Header("Location: http://".$server_name.$_SERVER['REQUEST_URI'].$separator."redirected_to_http=1");
    exit (0);
}
/**
 *  Add escape characters into string to it could be directly used in javascript
 *
 *  @param  string  $str
 *  @return string
 */
function js_escape($str){
    return str_replace("\n", '\n', addslashes($str));
}

/**
 *  Add GET parameter(s) to URL
 *
 *  Join URL with parameter useing '?' or '&' depending on whether URL already
 *  contain some parameters
 *
 *  @param  string  $url
 *  @param  string  $param
 *  @return string
 */
function add_param_to_url($url, $param){
    if (strpos($url, "?")) return $url."&".$param;
    else                   return $url."?".$param;
}


/**
 *  Clone array of objects
 *
 *  Create new array that contains new copies of object, not references
 *  to same objects
 *
 *  @param  array   $array
 *  @return array
 */
function clone_array($array){

    if (!is_array($array)) return $array;

    $clone = array();
    foreach($array as $k=>$v){
        if (is_object($v))  $clone[$k] = clone($v);
        else                $clone[$k] = $v;
    }

    return $clone;
}

/**
 * Set cookie, using $config->cookie_options
 *
 * @param string $name
 * @param string $value
 * @param array $options
 * @return bool
 */
function serwebSetCookie($name, $value, $options){
    global $config;

    $options = array_merge($config->cookie_options, $options);

    if (PHP_VERSION_ID < 70300){
        // Code for PHP < 7.3.0 - that does not support $options parameter and 'samesite' key
        $expires =  isset($options['expires'])  ? $options['expires']  : 0;
        $path =     isset($options['path'])     ? $options['path']     : "";
        $domain =   isset($options['domain'])   ? $options['domain']   : "";
        $secure =   isset($options['secure'])   ? $options['secure']   : false;
        $httponly = isset($options['httponly']) ? $options['httponly'] : false;

        return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }
    else{
        return setcookie($name, $value, $options);
    }

}