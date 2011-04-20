<?php


Class PearErrorException extends RuntimeException {
    var $pear_err;

    public function __construct($err_obj){
        parent::__construct();
        
        $this->pear_err = $err_obj;
    }
}


Class DBException extends PearErrorException {

}

Class XMLRPCException extends PearErrorException {

}

?>
