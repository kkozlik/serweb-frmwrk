<?php


Class PearErrorException extends RuntimeException {
    var $pear_err;

    public function __construct($err_obj){
        parent::__construct($err_obj->getMessage());

        $this->pear_err = $err_obj;
    }
}


Class DBException extends PearErrorException {

}

Class ApuConfigErrorException extends RuntimeException {
    var $apu;

    public function __construct($apu, $msg){
        parent::__construct($msg);
        $this->apu = $apu;
    }
}
