<?php
/**
 *  Extension for phplib object oriented html form
 *
 *  @author    Karel Kozlik
 *  @version   $Id: oohform_ext.php,v 1.8 2007/09/17 18:56:31 kozlik Exp $
 *  @package   serweb
 *  @subpackage framework
 */

/**
 *  Extension for phplib object oriented html form
 *
 *  @package   serweb
 *  @subpackage framework
 */
class form_ext extends form{
    /* set to true if type of submit element is hidden -> javascript function for submit form is generated */
    var $hidden_submits = array();
    var $hidden_cancels = array();
    var $form_name = '';
    var $form_cancels = array();


    /* add submit element to from
        $submit - associative array describing submit element

        Keys of $submit array:
            ['type']  - type of submit element 'hidden', 'button', 'image'
            ['text']  - text on button on alt on image
            ['src']   - source of image
            ['disabled'] - button is disabled
            ['class'] - CSS class
            ['extra_html'] - extra paramaters
     */

    function add_submit($submit){
        $this->add_extra_submit("okey", $submit);
    }

    function add_cancel($submit){
        $this->form_cancels[] = "cancel";
        $this->add_extra_submit("cancel", $submit);
    }

    function add_extra_cancel($name, $submit){
        $this->form_cancels[] = $name;
        $this->add_extra_submit($name, $submit);
    }

    function add_extra_submit($name, $submit){
        if (! empty($submit['class'])) $class = $submit['class'];
        else $class = null;

        if (! empty($submit['extra_html'])) $extra_html = $submit['extra_html'];
        else $extra_html = '';

        switch ($submit['type']){
        case "image":
            $element = array("type"=>"submit",
                             "name"=>$name,
                             "src"=>$submit['src'],
                             "disabled"=>!empty($submit['disabled']),
                             "class"=>$class,
                             "extrahtml"=>"alt='".$submit['text']."' ".$extra_html);

            /* if it is a cancel button, disable form validation */
            if (in_array($name, $this->form_cancels)) $element['extrahtml'] .= " onclick='this.form.onsubmit=null;'";
            break;

        case "button":
            $element = array("type"=>"submit",
                             "name"=>$name."_x",
                             "value"=>$submit['text'],
                             "disabled"=>!empty($submit['disabled']),
                             "class"=>$class,
                             "extrahtml"=>$extra_html);

            /* if it is a cancel button, disable form validation */
            if (in_array($name, $this->form_cancels)) $element['extrahtml'] = "onclick='this.form.onsubmit=null;'";
            break;

        case "hidden":
        default:
            $element = array("type"=>"hidden",
                             "name"=>$name."_x",
                             "value"=>'0',
                             "class"=>$class,
                             "extrahtml"=>$extra_html);

            if (in_array($name, $this->form_cancels)) $this->hidden_cancels = $name."_x";
            else $this->hidden_submits[] = $name."_x";
        }

        $this->add_element($element);

    }

    /**
     *  Retrun names of all hidden elements in the form
     *
     *  Elements used internaly by this class are skipped (hidden submits,
     *  hidden cancels and 'form_cancels')
     *
     *  @return array
     */
    function get_hidden_el_names(){
        return array_diff((array)$this->hidden,
                    array_merge($this->hidden_cancels,
                                $this->hidden_submits,
                                array("form_cancels")));
    }

    /**
     *  Retrun all hidden elements in the form as string
     *
     *  @return string
     */
    function get_hidden_els_as_string(){

        $str = "";
        $els = $this->get_hidden_el_names();

        foreach($els as $v){
            $str .= $this->get_element($v);
        }

        return $str;
    }


    function get_start($jvs_name="",$method="",$action="",$target="",$form_name="") {
        /* save form name */
        $this->form_name = $form_name;
        return parent::get_start($jvs_name, $method, $action, $target, $form_name);
    }


    function get_finish($after="",$before="") {
        $cancels = implode(" ", $this->form_cancels);
        $this->add_element(array("type"=>"hidden",
                                 "name"=>"form_cancels",
                                 "value"=>$cancels));


        $str = parent::get_finish($after, $before);

        /* if submit is hidden we must create javascript submit function which validate form */

        if (count($this->hidden_submits)){
            /* form_name must be set because it is part of name of the function */
            if ($this->form_name) {
                $str .= "<script language='javascript'>\n<!--\n";
                $str .= "function ".$this->form_name."_submit() {\n";
                $str .= "   ".$this->form_name."_submit_extra('okey');\n";
                $str .= "}\n";

                $str .= "function ".$this->form_name."_submit_extra(name) {\n";
                /* if validator is set, call it */
                if ($this->jvs_name) {
                    $str .= "  if (false != ".$this->jvs_name."_Validator(document.".$this->form_name.")) {\n";
                    $str .= "    document['".$this->form_name.".'+name+'_x'].value=1; \n";
                    $str .= "    document.".$this->form_name.".submit(); \n";
                    $str .= "  }\n";
                }
                /* otherwise only run submit */
                else {
                    $str .= "    document['".$this->form_name.".'+name+'_x'].value=1; \n";
                    $str .= "  document.".$this->form_name.".submit(); \n";
                }
                $str .= "}\n";

                $str .= "//-->\n</script>";

            }
        }

        return $str;
    }

    /**
     *  Transform associative arraty to array of options for select or radio element
     *
     *  @param  array   $arr
     *  @return array
     *  @static
     */
    function array_to_opt($arr){

        $options = array();
        foreach ($arr as $k=>$v){
            $options[] = array("value"=>$k, "label"=>$v);
        }

        return $options;
    }
}
