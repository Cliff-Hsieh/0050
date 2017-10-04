<?php
date_default_timezone_set("Asia/Taipei");
include_once("dataController.php");
include_once("DBController.php");

class stockController {
    public function __construct(){

    }

    public function index(){
        $file = $this->getStockFile();
        $this->insertData($file);
    }

    public function getStockFile(){
        $year = date("Y")-1911;
	$today = $year."/".date("m/d");
        $link = "http://www.twse.com.tw/exchangeReport/STOCK_DAY?response=csv&date=$today&stockNo=0050";
        $file = iconv('big-5', 'utf-8', file_get_contents($link, 'r'));
        return $file;
    }

    public function insertData($file){
        $dataList = explode(PHP_EOL, $file);        
        $db = new dataController;

        foreach($dataList as $data){
            $fields = str_getcsv($data);
            if(preg_match("/\d{3}\/\d{2}\/\d{2}/", $fields[0])){
                $date = $fields[0];
                $high = $fields[4];
                $low = $fields[5];
                $close = $fields[6];
                $data = array("date"=>$date, "high"=>$high, "low"=>$low, "close"=>$close);
                $db->insertData($data);
            }
        }
    }
}

$weekday = date("w", time());
if( $weekday > 0 && $weekday < 6 ){
    $stock = new stockController;
    $stock->index();
}
