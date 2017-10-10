<?php
include("DBController.php");

class dataController{
    function __construct(){
        $db = new DBController;
        $this->conn = $db->connect();
    }

    public function insertData($data, $tableName){
        $conn = $this->conn;
        $date = explode("/", $data['date']);
        $newDate = ($date[0] + 1911)."-".$date[1]."-".$date[2];

        $high = $data['high'];
        $low = $data['low'];
        $close = $data['close'];
        if( $tableName === 'taiex' ){
            $sql = "INSERT IGNORE INTO `$tableName` (`date`, `highest`, `lowest`, `close`) VALUES ('$newDate', $high, $low, $close)";
        }else{
            $yesterdayKD = $this->getYesterdayKD();
            $max = $this->getHighestPrice();
            $highest = ( $max > $high ) ? $max : $high;
            $min = $this->getLowestPrice();
            $lowest = ($min < $low ) ? $min : $low;
            $rsv = $this->getRSV($highest, $lowest, $close);
            $k = round((($yesterdayKD['k'] * 2/3) + ($rsv * 1/3)), 1);
            $d = round((($yesterdayKD['d'] * 2/3) + ($k * 1/3)), 1);

            $sql = "INSERT IGNORE INTO `$tableName` (`date`, `highest`, `lowest`, `close`, `k-value`, `d-value`) VALUES ('$newDate', $high, $low, $close, $k, $d)";
        }
        $conn->query($sql);
    }

    public function getData(){
        $conn = $this->conn;
        $sql = "SELECT * FROM `0050` ORDER BY `date` DESC LIMIT 9";
        $result = $conn->query($sql);
        $today = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $sql = "SELECT `k-value`, `d-value` FROM `0050` ORDER BY `date` DESC LIMIT 1 OFFSET 1";
        $result = $conn->query($sql);
        $row = mysqli_fetch_assoc($result);       
	$yesterdayK = $row['k-value'];
        $yesterdayD = $row['d-value'];

        $todayKD = $this->getKDValue($today, $yesterdayK, $yesterdayD);
    }

    public function getYesterdayKD(){
        $conn = $this->conn;
        $sql = "SELECT `k-value`, `d-value` FROM `0050` ORDER BY `date` DESC LIMIT 1";
        $result = $conn->query($sql);
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        return array("k" => $row['k-value'], "d" =>$row['d-value']);
    }

    public function getRSV($highest, $lowest, $close){
        echo "highest: $highest, lowest: $lowest, close: $close";
        $rsv = round(($close - $lowest) / ($highest - $lowest) * 100 );
        return $rsv;
    }

    public function getHighestPrice(){
       $conn = $this->conn;
       $sql = "SELECT MAX(highest) as `highest` FROM (SELECT * FROM `0050` ORDER BY `date` DESC LIMIT 8) as t1";
       $result = $conn->query($sql);
       $row = mysqli_fetch_assoc($result);
       return $row['highest'];
    }

    public function getLowestPrice(){
       $conn = $this->conn;
       $sql = "SELECT MIN(lowest) as `lowest` FROM (SELECT * FROM `0050` ORDER BY `date` DESC LIMIT 8) as t1";
       $result = $conn->query($sql);
       $row = mysqli_fetch_assoc($result);

       return $row['lowest'];
    }    


    public function checkDataExists($date, $tableName){
        $conn = $this->conn;
        $sql = "SELECT * FROM `$tableName` WHERE `date` = '$date'";

        $result = $conn->query($sql);
        if( $result->num_rows > 0 ){
            return true;
        }else{
            return false;
        }
    }

    public function getKDValue($data, $yesterdayK, $yesterdayD){
        $high = 0;
        $low = null;
        $close = 0;
        $rsv = 0;
 
        for($i = 0; $i < count($data); $i++){
            $high = ($data[$i]['highest'] > $high) ? $data[$i]['highest'] : $high;
            if( $low == null ){
                $low = $data[$i]['lowest'];
            }else{
                $low = ($data[$i]['lowest'] < $low) ? $data[$i]['lowest'] : $low;
            }
        }
        $close = $data[0]['close'];
        $rsv = ($close - $low ) / ($high - $low ) * 100;

        $k = round(($yesterdayK * 2/3) + ($rsv * 1/3), 1);
        $d = round(($yesterdayD * 2/3) + ($k * 1/3), 1);
    }
}
