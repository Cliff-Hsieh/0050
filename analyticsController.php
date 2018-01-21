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
        $this->kLowerBound = 20;
    }

    public function index(){
        $msg = array();
        $data = $this->getDataDay(3);

        $this->checkPassivation($data, $msg);
        $this->checkKD($data, $msg);
	$taiex = $this->getTaiex();
        $this->checkTaiex($taiex, $msg);

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

    public function getTaiex(){
        $DB = new DBController;
        $conn = $DB->connect();
        $sql = "SELECT count(*) FROM `taiex`";
        $row = $conn->query($sql);
        $total = mysqli_fetch_array($row)[0];
        
        $limit = 5;
        if ($total > 20){
          if($total > 60){
            $limit = 60;
          }else{
            $limit = 20;
          }
        }
        $sql = "SELECT * FROM `taiex` ORDER BY `date` DESC LIMIT $limit";
        $row = $conn->query($sql);
        return mysqli_fetch_all($row, MYSQLI_ASSOC);
    }

    public function checkKD($data, &$msg){
        $today = $data[0];
        $yesterday = $data[1];
        $kDiff = round($today['k-value'] - $yesterday['k-value'], 2);
        $dDiff = round($today['d-value'] - $yesterday['d-value'], 2);
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

   public function checkTaiex($taiex, &$msg){
      $msg[] = $taiex[0]['date']."收盤價: ".$taiex[0]['close'];
      if(count($taiex) > 5){
        $this->getTaiexAvg(5, $taiex, $msg);
      }
      if(count($taiex) > 20){
        $this->getTaiexAvg(20, $taiex, $msg);
      }
      if(count($taiex) >= 60){
        $this->getTaiexAvg(60, $taiex, $msg);
      }
   }
   
   public function getTaiexAvg($num, $taiex, &$msg){
     $str_array = array(5 => "週", 20 => "月", 60 => "季");
     $total = 0;
     $avg = 0;
       for($i = 0; $i < $num; $i++){
         $total += str_replace(',', '', $taiex[$i]['close']);
     }
     $avg = round(($total / $num), 2);
     $close = str_replace(',', '', $taiex[0]['close']);

     if($avg - $close > 0){
       $msg[] = "MA$num: $avg, 收盤價在".$str_array[$num]."線底下，可參考買進";
     }else{
       $msg[] = "MA$num: $avg, 收盤價在".$str_array[$num]."線以上，可參考保守";
     } 
   }
}

$weekday = date("w", time());
if( $weekday > 0 && $weekday < 6 ){
    $analysis = new analyticsController;
    $analysis->index();
}
