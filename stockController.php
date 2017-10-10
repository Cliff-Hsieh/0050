<?php
date_default_timezone_set("Asia/Taipei");
include_once("dataController.php");

class stockController {
    public function index(){
        $model = new dataController;
       
        $file = $this->getCSVFile('0050');
        $sortData = $this->sortData($file, '0050');
        foreach($sortData as $data){
            $date = explode("/", $data['date']);
            $newDate = ($date[0] + 1911)."-".$date[1]."-".$date[2];
            if(!$model->checkDataExists($newDate, '0050')){
                $model->insertData($data, '0050');
            }
        }

        $file = $this->getCSVFile('taiex');
        $sortData = $this->sortData($file, 'taiex');
        foreach($sortData as $data){
            $date = explode("/", $data['date']);
            $newDate = ($date[0] + 1911)."-".$date[1]."-".$date[2];
            
            if(!$model->checkDataExists($newDate, 'taiex')){
                $model->insertData($data, 'taiex');
            }
        }
    }

    public function getCSVFile($stockName){
        $link = '';
        $year = date("Y")-1911;
	$today = $year."/".date("m/d");
        if( $stockName === 'taiex' ){
            $link = "http://www.twse.com.tw/indicesReport/MI_5MINS_HIST?response=csv&date=$today";
        }else{
            $link = "http://www.twse.com.tw/exchangeReport/STOCK_DAY?response=csv&date=$today&stockNo=$stockName";
        }
        $file = iconv('big-5', 'utf-8', file_get_contents($link, 'r'));
        return $file;
    }

    public function sortData($file, $fileName){
        $sortData = array();
        $dataList = explode(PHP_EOL, $file);
        foreach($dataList as $data){
            $fields = str_getcsv($data);
            if(preg_match("/\d{3}\/\d{2}\/\d{2}/", $fields[0])){
                if( $fileName == "taiex"){
                    $date = $fields[0];
                    $high = $fields[2];
                    $low = $fields[3];
                    $close = $fields[4];
                }else{
                    $date = $fields[0];
                    $high = $fields[4];
                    $low = $fields[5];
                    $close = $fields[6];
                }
                $sortData[] = array("date"=>$date, "high"=>$high, "low"=>$low, "close"=>$close);
            }
        }
        return $sortData;
    }
}

$weekday = date("w", time());
if( $weekday > 0 && $weekday < 6 ){
    $stock = new stockController;
    $stock->index();
}
