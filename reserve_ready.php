<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;

    $title = '部屋のご予約';
    $body = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $resroom = array_fill(0,6,false);
    $roomno2 = array('0101','0102','0103','0201','0202','0203');   // 部屋No
    $resname = array();
    $price = 0;
    $ex = 1;

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
    }else{
        $errorFlg = true;
    }


    if(isset($_POST['day'])){
        $ymd = $_POST['day'];

        $year = substr($ymd,0,4);
        $month = substr($ymd,5,2);
        $day = substr($ymd,8);        

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

    }else{
        $errorFlg = true;
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
	<link href="reserve.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="reserve.js"></script>

	<title>確認画面</title>
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
        echo '<form action="reserve_finish.php" method="POST">';
        echo '<input type="hidden" name="day" value="' .$ymd. '">';
        echo '<table class="ready">';
        echo '<thead><th>部屋名</th><th>人数</th><th>代表者名様</th></thead><tbody>';

        for($i=0;$i<6;$i++){
            $r = $roomno[$i];
            $name = $roomno2[$i];                
    
            if(isset($_POST[$r])){
                $resroom[$i] = true;
                $leader = htmlspecialchars($_POST[$name]);
                echo '<input type="hidden" name="room[]" value="' .$r. '">';
                echo '<input type="hidden" name="num[]" value="' .$_POST[$r]. '">';
                echo '<input type="hidden" name="leader[]" value="' .$leader. '">';
                $pr = 8000 * $_POST[$r];
                echo '<input type="hidden" name="price[]" value="' .$pr. '">';
                $price += $pr;
            }

            echo '<tr><td class="troom">' .$roomno[$i]. '</td>';

            if($_POST[$name] == '×'){
                echo '<td colspan="2" class="tdouble">予約不可';
            }else if($resroom[$i] == true){
                echo '<td class="tnum">' .$_POST[$r]. '名様</td><td class="tname">' .$leader. '　様</td>';
            }else{
                echo '<td colspan="2" class="tdouble">空室';
            }

            echo '</td></tr>';
        }

        echo '</table>';

        // 最終金額を算出する
        $price = $price * $ex;

        echo '<table class="ready">';
        echo '<tr><th class="tm">ご請求先</td><td class="tname">' .$_SESSION['name']. '　様</td></tr>';
        echo '<tr><th class="tm">合計金額</th><td class="tname">' .$price. '円</td></tr></table>';
        echo '<p>以上で予約を承ります。宜しければ「予約」ボタンを押下して下さい。</p>';

    }

?>

</div>
<div class="rm">
    <button id="wback" class="button2 bcenter">戻る</button>
    <?php
        if($errorFlg == false){
            echo '<input type="submit" value="予約" class="button2">';
            echo '</form>';
        }
    ?>
    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</body>
</html>