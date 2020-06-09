<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;

    $title = '部屋の変更';
    $body = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $resroom = array_fill(0,6,false);
    $roomInfo = array();
    $orderIDs = array();
    $price = 0;
    $ex = 1;

    $date = '';
    $orderID = '';
    $oid = '';

    $checkFlg = array_fill(0,6,'changeOK');
    $chk = 0;

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
    }else{
        $errorFlg = true;
    }

    if(isset($_GET['date'])){
        $date = $_GET['date'];

        $year = substr($date,0,4);
        $month = substr($date,5,2);
        $day = substr($date,8);        

    }else{
        $errorFlg = true;
    }

    if(isset($_GET['chID'])){
        $orderID = substr($_GET['chID'],0,8);
    }else{
        $errorFlg = true;
    }

    for($h=0;$h<6;$h++){
        $roomInfo[$h] = array(0, 0, 0);
    }


    if($errorFlg == true){
        $title = 'エラー';
    }

    function spDays($mo,$da){
        $s = false;
        $spd = array('0101','0102','0103','0429','0430','0501','0502','0503','0504','0505','0506','0810','0811','0812','0813','0814','0815','1229','1230','1231');
        foreach($spd as $value){
            if($value == $mo.$da){
                $s = true;
                break;
            }
        }
        return $s;
    }

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="reserve_change.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="reserve_change.js"></script>

	<title>予約の変更</title>
</head>
<body>
<header>
<h1><?php echo $title; ?></h1>
</header>
<div class="subwindow rm">
<?php
    if($errorFlg == true){
        echo '<p>エラーが発生しました。<br>お手数ですがもう一度やり直してください。</p>';
    }else{

        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $sql= 'SELECT roomno,orderID,num,leader,customerID FROM reserve WHERE date = "' .$date. '" ORDER BY roomno ASC';
        
            $statement = $pdo->prepare($sql);
        
            $statement->execute();

            $emptyFlg = true;
            $g = 0;
                
            while($row = $statement->fetch()){

                for($i=0;$i<6;$i++){
                    if($row[0] == $roomno[$i]){
                        if($row[4] == $customerID){
                            if($emptyFlg == true){
                                $orderIDs[0] = $row[1];
                            }else{
                                if(!in_array($row[1],$orderIDs)){
                                    $orderIDs[$g] = $row[1];
                                }                                
                            }
                            $g++;
                            $roomInfo[$i] = array($row[1], $row[2], $row[3]);
                            $emptyFlg = false;                            
                            break;
                        }else{
                            $roomInfo[$i] = array(0, -1, 0);
                            $checkFlg[$i] = 'changeNG';
                            break;
                        }                        
                    }
                }
            }
        
            if($emptyFlg == true){
                echo '<p>エラーが発生しました。<br>お手数ですがもう一度やり直してください。</p>';
            }        
        
        }catch(PDOException $e){
                $title = 'データベースエラー';
                $body = '<p>データベース読み込みにエラーが発生しました</p>';
        }
        
        $pdo = NULL;

        echo '<form action="change_ready.php" method="POST">';
        echo '<input type="hidden" name="day" value="' .$date. '">';
        echo '<table class="ready">';
        echo '<thead><th class="troom">部屋名</th><th class="tnum">人数</th><th class="tname">代表者名様</th><th class="torder">注文番号/ｷｬﾝｾﾙ</th></thead><tbody>';

        for($j=0;$j<6;$j++){
            $cnum = $roomno[$j]. '-num';
            $cname = $roomno[$j]. '-name';
            $cdel = $roomno[$j]. '-can';

            echo '<tr><td class="troom">';
            if($roomInfo[$j][1] != -1){
                echo '<input type="checkbox" name="change[]" class="' .$checkFlg[$j]. ' ' .$roomno[$j]. '" value="' .$roomno[$j]. '"> ';
            }

            echo $roomno[$j]. '</td>';
            
            if($roomInfo[$j][1] == -1){
                echo '<td colspan="3" class="triple">予約不可</td></tr>';
            }else{
                echo '<td class="tnum"><select name="' .$cnum. '" id="' .$cnum. '" class="Rnum">';
                if($roomInfo[$j][1] == 2){
                    echo '<option value="1">1</option><option value="2" selected>2</option>';
                }else if($roomInfo[$j][1] == 1){
                    echo '<option value="1" selected>1</option><option value="2">2</option>';
                }else{
                    echo '<option value="1">1</option><option value="2">2</option>';
                }
                echo '</select>名様</td>';
                echo '<td class="tname"><input type="text" name="' .$cname. '" id="'.$cname. '" class="Rname"';
                if($roomInfo[$j][1] != 0){
                    echo ' value="' .$roomInfo[$j][2]. '"';
                }
                echo ' pattern="(?=.*?[\u30A1-\u30FC])[\u30A1-\u30FC\s]*">様</td><td class="torder" required>';
                if($roomInfo[$j][1] != 0){
                    echo '<input type="checkbox" name="cancel[]" id="' .$cdel. '" class="Rcancel" value="' .$roomno[$j]. '"';
                    /*
                    if($chk == 0 && $orderID == $roomInfo[$j][0]){
                        echo ' checked="checked"';
                    }
                    */
                    echo '> ' .$roomInfo[$j][0];
                    if($oid == ''){
                        $oid = $roomInfo[$j][0];
                    }else if($oid != $roomInfo[$j][0]){
                        $chk++;
                    }
                    
                }else{
                    echo '-';
                }
                echo '</td></tr>';

            }
        }

        echo '</table>';

        if($chk > 1){
            echo '<p><input type="checkbox" name="conpile" id="conpile" value="on">注文番号をまとめる<br>';
            echo '<select name="order" id="orderConpile" class="' .$orderID. '">';
            foreach($orderIDs as $values){
                echo '<option value="' .$values. '"';
                if($values == $orderID){
                    echo ' selected';
                }
                echo '>' .$values. '</option>';                
            }
            echo '</select></p>';
        }

        echo '<p>宿泊部屋の追加・変更を行う部屋にチェックを入れてご記入ください。<br>予約のキャンセルは注文番号の左にチェックを入れてください。<br>この先近日実装します</p>';

    }

?>

</div>
<div class="rm">
    
    <?php
        if($errorFlg == false){
            echo '<input type="submit" value="変更" class="button2" id="csend">';
            echo '</form>';
        }
    ?>

<button id="wback" class="button2 bcenter">戻る</button>

    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</body>
</html>