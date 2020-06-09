<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;
    $resError = false;

    $title = '予約完了しました';
    $body = '';
    $message = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $resroom = array_fill(0,6,false);
    $roomno2 = array('0101','0102','0103','0201','0202','0203');   // 部屋No
    $resname = array();

    $resData = array();

    $aname = '';
    $price = 0;
    $orderID = '';
    $email = '';
    $orderRoom = array('AA','AB','AC','BA','BB','BC');   // 部屋No

    $successInfo = array();

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
        $aname = $_SESSION['name'];
    }else{
        $errorFlg = true;
    }

    if(isset($_POST['day'])){
        $ymd = $_POST['day'];

        $year = substr($_POST['day'],0,4);
        $month = substr($_POST['day'],5,2);
        $day = substr($_POST['day'],8);        

        // 予約可能かどうかのチェック（当日より30日以内であれば登録可能）
        $now = strtotime(date('Y-m-d'));
        $target = strtotime($_POST['day']);
        $days = ($target - $now) / (60*60*24);
        
        if($days >= 30){
            $errorFlg = true;
        }

    }else{
        $errorFlg = true;
    }

    // reserve_ready.phpよりデータを受け取る

    if(isset($_POST['room'])){
        $ad = 0;
        foreach($_POST['room'] as $ro){
            $resData[$ad][0] = $ro;

            // 注文番号をセットする
            for($i=0;$i<6;$i++){
                if($ro == $roomno[$i] && $orderID == ''){
                    $orderID = substr($year,2,4). $month. $day. $orderRoom[$i];
                }
            }
            $ad++;
        }

        $ad = 0;
        foreach($_POST['leader'] as $ld){
            $resData[$ad][1] = $ld;
            $ad++;
        }

        $ad = 0;
        foreach($_POST['num'] as $nu){
            $resData[$ad][2] = $nu;
            $ad++;
        }

        $ad = 0;
        foreach($_POST['price'] as $pr){
            $resData[$ad][3] = $pr;
            $ad++;
            $price += $pr;
        }

    }

/*
    for($i=0;$i<6;$i++){
        $r = $roomno[$i];
        $name = $roomno2[$i];    

        if(isset($_POST[$r])){
            $lists[$n] = array($r, $_POST[$name], $_POST[$r]);
            
            $n++;

            if($orderID == ''){
                $orderID = substr($year,2,4). $month. $day. $orderRoom[$i];
            }
            
        }
    }
*/

    if($ad == 0){
        $errorFlg = true;
    }

    if($errorFlg == false){
        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $m = 0;

            foreach($resData as $val){
                // 予約情報の重複チェック
                $sql1 = 'SELECT * FROM reserve WHERE date = "' .$ymd. '" AND roomno = "' .$val[0]. '"';

                $st = $pdo->query($sql1);
                $overlap = $st->fetchall();

                if(count($overlap) == 0){
                    $sql2 = 'INSERT INTO reserve SET date = ?, roomno = ?, customerID = ?, leader = ?, num = ?, orderID = ?, price = ?';

                    $statement = $pdo->prepare($sql2);

                    $statement->bindValue(1, $ymd);
                    $statement->bindValue(2, $val[0]);
                    $statement->bindValue(3, $customerID);
                    $statement->bindValue(4, $val[1]);
                    $statement->bindValue(5, $val[2]);
                    $statement->bindValue(6, $orderID);
                    $statement->bindValue(7, $val[3]);

                    $pdo->beginTransaction();
            
                    $statement->execute();
                    
                    $pdo->commit();

                    $successInfo[$m] = <<< EOS
部屋番号：{$val[0]}
代表者様：{$val[1]}様
宿泊人数：{$val[2]}名様
======

EOS;

                    $m++;

                }else{
                    $resError = true;
                    $message .= 'エラー！部屋：' .$val[1]. 'が予約できませんでした。<br>';
                }
            }

            $sql3 = 'SELECT email FROM member where customerID ="' .$customerID. '"';
            $st1 = $pdo->prepare($sql3);
            $st1->execute();
            $email = $st1->fetchColumn();    
    
        }catch(PDOException $e){
                $title = 'データベースエラー';
                $body = '<p>データベース読み込みにエラーが発生しました</p>';
        }
    }else{
        $title = 'エラー';
    }

    $pdo = NULL;

    if($m>0){
        

        $mailto = $email;

        //Return-Pathに指定するメールアドレス
	    $returnMail = 'info@hogehoge.com';

        $sname = '予約システム';
        $mail = 'info@hogehoge.com';
        $subject = '予約完了のお知らせ';
    
        $mailContent1 = <<< EOM1
{$aname}　様
このたびは予約いただき、ありがとうございました。

予約情報つきましては、以下をご確認ください。
予約の変更、キャンセルを行う際は、「予約番号」が必要となりますので大切に保管して下さい。
弊社ホームページよりログイン頂き、「変更」ボタンより変更・キャンセルを行えます。

予約番号：{$orderID}
予約日：{$year}年{$month}月{$day}日
~~~~~~

EOM1;

$mailContent2 = <<< EOM2

料金：{$price}円

お客様のご来所を心よりお待ちしております。

======
予約システム
https://twinklesky.biz/fl/reserve/
EOM2;

        $body = $mailContent1;
        foreach ($successInfo as $v) {
            $body .= $v;
        }
        $body .=$mailContent2;

        mb_language('ja');
        mb_internal_encoding('UTF-8');

        //Fromヘッダーを作成
        $header = 'From: ' . mb_encode_mimeheader($sname). ' <' . $mail. '>';

        if (mb_send_mail($mailto, $subject, $body, $header, '-f'. $returnMail)) {
            $message .= '予約が完了いたしました。<br>内容につきましては、メールをご確認ください。';
        
        } else {
            $errors['mail_error'] = "メールの送信に失敗しました。";
        }
    }
?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="reserve.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="login.js"></script>

	<title><?php echo $title; ?></title>
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
        echo $message;
    }

?>

</div>
<div class="rm">
    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</body>
</html>