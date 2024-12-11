<?php

/**
 * Extension to php build in PDO classes.
 * Main purpose is to include executed statement to PDOException for logging purpose.
 *
 * It's inspired by:
 * https://stackoverflow.com/questions/47187147/is-there-any-way-to-get-sql-statement-from-pdoexception
 */
class Serweb_PDO extends PDO{

    public function __construct(...$params){

        try{
           parent::__construct(...$params);
        }
        catch(PDOException $e){
            throw new PDOException('Cannot connect to database: '.$e->getMessage(), $e->getCode(), $e);
        }

        //Set connection to raise exception when error
        $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        //Set connection to use the derived class Serweb_PDOStatement instead of PDOStatement for statements
        $this->setAttribute( PDO::ATTR_STATEMENT_CLASS, ['Serweb_PDOStatement']);

   }

    /**
     * Overrides exec() method saving the statement to the thrown exception
     */
    public function exec(string $statement): int|false{
        try {
            $t = parent::exec($statement);
        }
        catch (PDOException $e) {
            $e->query =  $statement;
            throw $e;
        }
        return $t;
    }

    /**
     * Overrides query() method saving the statement to the thrown exception
     */
    public function query($statement, ...$params){
        try {
            $t = parent::query($statement, ...$params);
        }
        catch (PDOException $e) {
            $e->query =  $statement;
            throw $e;
        }
        return $t;
    }
}

class Serweb_PDOStatement extends PDOStatement{

   protected $_debugValues = null;
   protected $_ValuePos = 0;

   /**
    * overrides execute saving array of values and catching exception with error logging
    *
    * @param array $values
    * @return bool              Returns TRUE on success or FALSE on failure.
    */
   public function execute(?array $values = null) : bool{

      $this->_ValuePos    = 0;
      if (!is_null($values)) $this->_debugValues = $values;

      try {
         $t = parent::execute($values);
      }
      catch (PDOException $e) {
          $e->query =  $this->_debugQuery();
         throw $e;
      }

      return $t;
   }

   /**
    * Overrides bindValue function and save the param values into internal var
    *
    * @param string|integer $param
    * @param mixed $value
    * @param [type] $type
    * @return boolean
    */
   public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool{
      // Add the optional colon if $param do not contain it
      $key = $param[0]==':' ? $param : ":$param";
      $this->_debugValues[$key] = $value;

      return parent::bindValue($param, $value, $type);
   }

   /**
    * Overrides bindParam function and save the param values into internal var
    *
    * @param string|integer $param
    * @param mixed $var
    * @param integer $type
    * @param integer $maxLength
    * @param mixed $driverOptions
    * @return boolean
    */
   public function bindParam(string|int $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool{
      // Add the optional colon if $param do not contain it
      $key = $param[0]==':' ? $param : ":$param";
      $this->_debugValues[$key] = &$var;

      return parent::bindParam($param, $var, $type, $maxLength, $driverOptions);
   }

   /**
    * Retrieves query text with values for placeholders
    *
    * @param boolean $replaced
    * @return string
    */
   protected function _debugQuery($replaced = true){

      $q = $this->queryString;

      if (!$replaced) return $q;

      return preg_replace_callback('/(:([0-9a-z_]+)|(\?))/i', array(
         $this,
         '_debugReplace'
      ), $q);
   }

   /**
    * Replaces a placeholder with the corresponding value
    *
    * @param string $m  name of a placeholder
    * @return string
    */
   protected function _debugReplace($m){

      if ($m[1] == '?') {
         $v = $this->_debugValues[$this->_ValuePos++];
      }
      else {
         $v = $this->_debugValues[$m[1]];
      }
      if ($v === null) {
         return "NULL";
      }
      if (!is_numeric($v)) {
         $v = str_replace("'", "''", $v);
      }

      return "'" . $v . "'";
   }
}

