<?php
include("DBController.php");

class dataController{

    public function insertData($data){
        $db = new DBController;
        $conn = $db->connect();
        $date = explode("/", $data['date']);
        $newDate = ($date[0] + 1911)."-".$date[1]."-".$date[2];

        if(!$this->checkDataExists($conn, $newDate)){
            $high = $data['high'];
            $low = $data['low'];
            $close = $data['close'];
            $yesterdayKD = $this->getYesterdayKD($conn);
            $max = $this->getHighestPrice($conn);
            $highest = ( $max > $high ) ? $max : $high;
            $min = $this->getLowestPrice($conn);
            $lowest = ($min < $low ) ? $min : $low;
            $rsv = $this->getRSV($highest, $lowest, $close);
            $k = round((($yesterdayKD['k'] * 2/3) + ($rsv * 1/3)), 1);
            $d = round((($yesterdayKD['d'] * 2/3) + ($k * 1/3)), 1);

            $sql = "INSERT IGNORE INTO `0050` (`date`, `highest`, `lowest`, `close`, `k-value`, `d-value`) VALUES ('$newDate', $high, $low, $close, $k, $d)";
            $conn->query($sql);
        }
    }

    public function getData($conn){
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

    public function getYesterdayKD($conn){
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

    public function getHighestPrice($conn){
       $sql = "SELECT MAX(highest) as `highest` FROM (SELECT * FROM `0050` ORDER BY `date` DESC LIMIT 8) as t1";
       $result = $conn->query($sql);
       $row = mysqli_fetch_assoc($result);
       return $row['highest'];
    }

    public function getLowestPrice($conn){
       $sql = "SELECT MIN(lowest) as `lowest` FROM (SELECT * FROM `0050` ORDER BY `date` DESC LIMIT 8) as t1";
       $result = $conn->query($sql);
       $row = mysqli_fetch_assoc($result);

       return $row['lowest'];
    }    


    public function checkDataExists($conn, $date){
        $sql = "SELECT * FROM `0050` WHERE `date` = '$date'";
        $result = $conn->query($sql);
        if( $result->num_rows > 0 ){
            return true;
        }else{
            return false;
        }
    }

    /*
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
        echo "k: $k, d: $d";
    }
    */
}
