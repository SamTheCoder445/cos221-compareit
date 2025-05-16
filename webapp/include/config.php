<?php
class Database{
   private static $instance = null;
   private $connection;

   // db config
   private const DB_HOST = '';
   private const DB_USER = '';
   private const DB_PASS = '';
   private const DB_NAME = '';

   private function __construct(){
       $this->connection = new mysqli(
           self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME
       );
       if($this->connection->connect_error){
           throw new Exception("Database connection failed: " . $this->connection->connect_error);
       }

       $this->connection->set_charset("utf8mb4");
   }

   public static function getInstance(){
       if(self::$instance===null){
           self::$instance = new self();
       }
       return self::$instance;
   }

   public function getConnection(){
       return $this->connection;
   }

   public function __clone(){}
   public function __wakeup(){}

}
?>
