<?php
include_once("DBController.php");

class analyticsController{
    public function index(){
        $mail = new mailController;
        $msg = array();
        global $market;
        $data = $this->getDataDay(3);
        $this->checkKD($data, $msg);
        $mail->sendMail($msg);
    }

    public function getDataDay($days){
        $DB = new DBController;
        $conn = $DB->connect();

        $sql = "SELECT * FROM `0050` ORDER BY `date` DESC LIMIT $days";
        $row = $conn->query($sql);
        return mysqli_fetch_all($row, MYSQLI_ASSOC);
   }

   public function checkKD($data, &$msg){
      $today = $data[0];
      $yesterday = $data[1];
      $msg[] = "K: ".$today['k-value']." D: ".$today['d-value'];

      //黃金交叉
      if(($yesterday['d-value'] > $yesterday['k-value']) && ($today['k-value'] > $today['d-value'])){
          $msg[] = "出現黃金交叉，可考慮買進";
      }

      //死亡交叉
      if(($yesterday['k-value'] > $yesterday['d-value']) && ($today['d-value'] > $today['k-value'])){
          $msg[] = "出現死亡交叉，可考慮賣出";
      }
   }

   //檢查為多頭或是空頭
   /*
      收盤價 > 5MA > 10MA :多頭市場
      收盤價 < 5MA < 10MA :空頭市場
      5MA < 收盤價 < 10MA :盤整市場

   */
   public function checkMarket(){

   }
}

$analysis = new analyticsController;
$analysis->index();
