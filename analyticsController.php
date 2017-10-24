<?php
include_once("DBController.php");
include_once("mailController.php");

class analyticsController{
    public $market;
    public $passivation;
    public $kUpperBound; 

    public function __construct()
    {
        $this->market = "normal";
        $this->passivation = false;
        $this->kUpperBound = 80;
    }

    public function index(){
        $msg = array();
        $data = $this->getDataDay(3);

        $this->checkPassivation($data, $msg);
        $this->checkKD($data, $msg);

        $mail = new mailController;
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
        $kDiff = $today['k-value'] - $yesterday['k-value'];
        $dDiff = $today['d-value'] - $yesterday['d-value'];
        $msg[] = "K: ".$today['k-value']."($kDiff) , D: ".$today['d-value']."($dDiff)";

        if(($this->passivation === false) && ($today['k-value'] > $this->kUpperBound)){
            $msg[] = "K值超過上界，建議賣出";
        }

        //golden cross
        if(($yesterday['d-value'] > $yesterday['k-value']) && ($today['k-value'] > $today['d-value'])){
            $msg[] = "出現黃金交叉，可考慮買進";
        }

        //death cross
        if(($yesterday['k-value'] > $yesterday['d-value']) && ($today['d-value'] > $today['k-value'])){
            $msg[] = "出現死亡交叉，可考慮賣出";
        }
    }

   //檢查為多頭或是空頭
   /*
       多頭: 大盤在季線之上(MA60)，且趨勢往上走
       空頭: 大盤在季線以下，且趨勢往下走
   */
   public function checkMarket(){

   }

   //檢查鈍化
   //空頭市場, k-upper下降到70, k-lower下降到10, 大盤在9000點之上的話k-upper下修到60
   public function checkPassivation($data, &$msg){
       if(($data[0]['k-value'] > 80) && ($data[1]['k-value'] > 80) && ($data[2]['k-value'] > 80)){
           $msg[] = "目前高檔鈍化中，未來很有機會繼續漲";
           $this->passivation = true;
       }
       if(($data[0]['k-value'] < 20) && ($data[1]['k-value'] < 20) && ($data[2]['k-value'] < 20)){
           $msg[] = "目前低檔鈍化中，未來很有機會繼續跌";
       }
   }
}

$weekday = date("w", time());
if( $weekday > 0 && $weekday < 6 ){
    $analysis = new analyticsController;
    $analysis->index();
}
