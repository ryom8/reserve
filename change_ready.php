<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;
    $emptyFlg1 = false;
    $emptyFlg2 = false;

    $title = '部屋の変更';
    $body = '';

    $year = 0;
    $month = 0;
    $day = 0;

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $resroom = array_fill(0,6,false);
    $roomInfo = array();
    $orderIDs = array();
    $price = 0;
    $cprice = 0;
    $ex = 1;

    $change = array();
    $chg = 0;
    $changes = array();
    $chs = 0;
    $cancel = array();
    $canSend = array();

    $inError = array();
    $e = 0;
    $changeNum = 0;
    $cancelRatio = 0;

    $conpileOrder = '';
    $defaultID = '';

    $date = '';
    $orderID = '';

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
    }else{
        $errorFlg = true;
    }

    if(isset($_POST['cancel'])){
        $cancel = $_POST['cancel'];
    }

    if(isset($_POST['change'])){
        foreach($_POST['change'] as $fc){
            $change[$chg] = array($fc,false);
            $chg++;
        }

        foreach($change as $val){
            if(!in_array($val,$cancel)){
                $cTarget1 = $val[0] . '-num';
                $cTarget2 = $val[0] . '-name';
                if(isset($_POST[$cTarget2]) && $_POST[$cTarget2] != ''){
                    $changes[$chs] = array($val[0],$_POST[$cTarget1],htmlspecialchars($_POST[$cTarget2]));
                    $chs++;
                }else{
                    $inError[$e] = array($val[0],'noLeader');
                    //echo $inError[$e][0];
                    $e++;
                }
            }else{
                $inError[$e] = array($val[0],'checkErr');
                //echo $inError[$e][0];
                $e++;
            }
            $changeNum++;            
        }
    }

    if(isset($_POST['day'])){
        $date = $_POST['day'];

        $year = substr($date,0,4);
        $month = substr($date,5,2);
        $day = substr($date,8);        

        // 予約可能かどうかのチェック（当日より30日以内であれば登録可能）
        $now = strtotime(date('Y-m-d'));
        $target = strtotime($_POST['day']);
        $days = ($target - $now) / (60*60*24);
        
        if($days >= 30){
            $errorFlg = true;
        }else{
            // 曜日・特別料金であるかどうかの判定を行う
            $week = date('w', mktime(0, 0, 0, $month, $day, $year));
            
            // 金額の算出の判定（週末であるか、大型連休であるか）
            if(spDays($month,$day) == true){
                $ex = 1.5;
            }else if($week >= 5 || $week == 0){
                $ex = 1.25;
            }
        }

        

        // キャンセル料金の算出
        if($days > 4){
            $cancelRatio = 0;
        }else if($days > 1){
            $cancelRatio = 0.5;
        }else{
            $cancelRatio = 1;
        }

    }else{
        $errorFlg = true;
    }

    // 注文番号をまとめる場合
    if(isset($_POST['conpile'])){
        if(isset($_POST['order'])){
            $conpileOrder = $_POST['order'];
        }
    }

    if(empty($change) && empty($cancel)){
        if(!isset($_POST['conpile'])){
            $emptyFlg = true;
        }
    }

    if($errorFlg == true){
        $title = '入力エラー';
        $body = '入力に不備があります。入力をやり直してください。';
    }

    if($emptyFlg == true){
        $title = '変更エラー';
        $body = '予約の変更・キャンセルが入力されていません！';

    }else{
        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $sql= 'SELECT roomno,orderID,num,leader,price FROM reserve WHERE date = "' .$date. '" AND customerID = "' .$customerID. '" ORDER BY roomno ASC';
        
            $statement = $pdo->prepare($sql);
        
            $statement->execute();

            // 変更・キャンセルの項目の抽出
            $changeCol = array();
            $cancelCol = array();
            $ch = 0;
            $ca = 0;

            // 変更のデータを照合

            while($row = $statement->fetch()){

                // 変更データ
                foreach($change[0] as $c){
                    if($c == $row[0]){
                        $changeCol[$ch] = array($row[0],$row[1],$row[2],$row[3],$row[4]);
                        $ch++;
                        break;
                    }
                }

                // キャンセルデータ
                foreach($cancel as $d){
                    if($d == $row[0]){
                        $pr = $row[2] * 8000 * $ex;
                        $cprice += $pr;
                        $cancelCol[$ca] = array($row[0],$row[1],$row[2],$row[3]);
                        $canSend[$cc] = '<input type="hidden" name="cancel_room[]" value=' .$row[0]. '>';
                        $canSend[$cc] .= '<input type="hidden" name="cancel_order[]" value=' .$row[1]. '>';
                        $canSend[$cc] .= '<input type="hidden" name="cancel_price[]" value=' .$pr. '>';
                        $ca++;
                        break;
                    }
                }

                // 追加された宿泊データを抽出する
                for($i=0;$i<$changeNum;$i++){
                    if($change[$i][0] == $row[0]){
                        $change[$i][1] = true;
                        break;
                    }
                }

                if($conpileOrder == '' && $defaultID == ''){
                    $defaultID = $row[1];
                }

            }
            
            /*
            if($ch == 0 && $ca == 0){
                $errorFlg = true;
            }
            */
        
        }catch(PDOException $e){
                $title = 'データベースエラー';
                $body = '<p>データベース読み込みにエラーが発生しました</p>';
        }
        
        $pdo = NULL;

        $cprice = $cprice * $cancelRatio;

        if($conpileOrder != ''){
            $defaultID = $conpileOrder;
        }       

        // 変更された部屋の処理

        $chaCol = array();
        $chaSend = array();
        $chFlg = false;
        $cc = 0;
        
        for($i=0;$i<$changeNum;$i++){
            if($change[$i][1] == true){
                foreach($changes as $value2){
                    if($value2[0] == $change[$i][0]){                            
                                    
                        foreach($changeCol as $value3){
                            $cpr = ($value2[1] -$value3[2]) * 8000 * $ex;
                            $price += $cpr;
                            if($value2[0] == $value3[0]){                                
                                if($conpileOrder == ''){
                                    $oid = $value3[1];
                                }else{
                                    $oid = $conpileOrder;
                                }
                            }
                            $chaCol[$cc] = array($value2[0],$oid,$value2[1],$value2[2],$cpr);
                            $chaSend[$cc] = '<input type="hidden" name="change_room[]" value=' .$value2[0]. '>';
                            $chaSend[$cc] .= '<input type="hidden" name="change_num[]" value=' .$value2[1]. '>';
                            $chaSend[$cc] .= '<input type="hidden" name="change_name[]" value="' .$value2[2]. '">';
                            $chaSend[$cc] .= '<input type="hidden" name="change_price[]" value=' .$cpr. '>';

                        }
                        $cc++;
                        $chFlg = true;
                    }
                }        
           }
        }

       
        // 追加された部屋の処理
        
        $addCol = array();
        $addSend = array();
        $adFlg = false;
        $ad = 0;
     
        for($i=0;$i<$changeNum;$i++){
            if($change[$i][1] == false){
                foreach($changes as $value2){
                    if($value2[0] == $change[$i][0]){
                        $apr = 8000 * $value2[1] * $ex;
                        $price += $apr;
                        $addCol[$ad] = array($value2[0],$defaultID,$value2[1],$value2[2]);
                        $addSend[$ad] = 'abcd<input type="hidden" name="add_room[]" value="' .$value2[0]. '">';
                        $addSend[$ad] .= '<input type="hidden" name="add_order[]" value="' .$defaultID. '">';
                        $addSend[$ad] .= '<input type="hidden" name="add_num[]" value=' .$value2[1]. '>';
                        $addSend[$ad] .= '<input type="hidden" name="add_name[]" value="' .$value2[2]. '">';
                        $addSend[$ad] .= '<input type="hidden" name="add_price[]" value=' .$apr. '>';
                        $ad++;
                        $adFlg = true;
                    }
                }
        
            }
        }
              
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
    <script type="text/javascript" src="reserve_ready.js"></script>

	<title>変更のご確認</title>
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
        echo '<form action="change_finish.php" method="POST">';
        echo '<input type="hidden" name="day" value="' .$date. '">';

        echo '<p>以下の通り予約の変更を行います。ご確認の上、変更ボタンを押下して下さい。</p>';

        if($chFlg == true){
            echo '<h3>＜宿泊の変更＞</h3>';
            createTable($chaCol);
            foreach($chaSend as $cs){
                echo $cs;
            }
        }

        if($adFlg == true){
            echo '<h3>＜宿泊の追加＞</h3>';
            createTable($addCol);
            foreach($addSend as $as){
                echo $as;
            }
        }

        if($ca != 0){
            echo '<h3>＜宿泊のキャンセル＞</h3>';
            createTable($cancelCol);
            foreach($canSend as $cas){
                echo $cas;
            }
        }

        if($conpileOrder != ''){            
            echo '<p>注文番号を <b>' .$conpileOrder. '</b> にまとめます</p>';
            echo '<input type="hidden" name="cOrder" value="' .$conpileOrder. '">';
        }

        if($cprice != 0){
            echo '<p>キャンセル料： <b>\\' .$cprice. '</b></p>';
        }

        echo '<p>合計金額： <b>\\' .$price. '</b></p>';
    }


function createTable($data){
    echo '<table class="ready">';
    echo '<thead><th class="troom">部屋名</th><th class="tnum">人数</th><th class="tname">代表者名様</th><th class="torder">注文番号</th></thead><tbody>';

    foreach($data as $d){
        echo '<tr><td class="troom">' .$d[0]. '</td>';
        echo '<td class="tnum">' .$d[2]. '</td>';
        echo '<td class="tname">' .$d[3]. ' 様</td>';
        echo '<td class="">' .$d[1]. '</td></tr>';
    }
    echo '</table>';
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