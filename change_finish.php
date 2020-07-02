<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;
    $resError = false;

    $title = '変更完了しました';
    $body = '';
    $msg = '変更に失敗しました。お手数ですが、再度やり直してください。';
    $message = array();
    $me = 0;
    $email = '';

    $add = 0;
    $addCol = array();
    $addPrice = 0;

    $cha = 0;
    $changeCol = array();

    $can = 0;
    $cancelCol = array();

    $re = 0;
    $resData = array();

    $conpileOrder = '';

    $err = 0;

    $roomno = array('101','102','103','201','202','203');   // 部屋No

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
        $name = $_SESSION['name'];
    }else{
        $errorFlg = true;
    }

    if(isset($_POST['day'])){
        $ymd = $_POST['day'];

        $year = substr($_POST['day'],0,4);
        $month = substr($_POST['day'],5,2);
        $day = substr($_POST['day'],8);
        
        $date = $year.$month.$day;

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

    if(isset($_POST['cOrder'])){
        $conpileOrder = $_POST['cOrder'];
    }

    if(isset($_POST['add_room'])){
        foreach($_POST['add_room'] as $adr){
            $addCol[$add][0] = $adr;
            $add++;
        }

        $add = 0;
        foreach($_POST['add_order'] as $ado){
            $addCol[$add][1] = $ado;
            $add++;
        }

        $add = 0;
        foreach($_POST['add_num'] as $adn){
            $addCol[$add][2] = $adn;
            $add++;
        }

        $add = 0;
        foreach($_POST['add_name'] as $adna){
            $addCol[$add][3] = $adna;
            $add++;
        }

        $add = 0;
        foreach($_POST['add_price'] as $adp){
            $addCol[$add][4] = $adp;
            $addPrice += $adp;
            $add++;
        }        
    }

    if(isset($_POST['cancel_order'])){
        foreach($_POST['cancel_room'] as $car){
            $cancelCol[$can][0] = $car;
            $can++;
        }

        $can = 0;
        foreach($_POST['cancel_order'] as $cao){
            $cancelCol[$can][1] = $cao;
            $can++;
        }

        $can = 0;
        foreach($_POST['cancel_price'] as $cap){
            $cancelCol[$can][2] = $cap;
            $can++;
        }        
    }


    if(isset($_POST['change_room'])){

        foreach($_POST['change_room'] as $chr){
            $changeCol[$cha][0] = $chr;
            $cha++;
        }

        $cha = 0;
        foreach($_POST['change_num'] as $chn){
            $changeCol[$cha][1] = $chn;
            $cha++;
        }

        $cha = 0;
        foreach($_POST['change_name'] as $chna){
            $changeCol[$cha][2] = $chna;
            $cha++;
        }

        $cha = 0;
        foreach($_POST['change_price'] as $chp){
            $changeCol[$cha][3] = $chp;
            $cha++;
        }
    }

    if($errorFlg == false){
        try{
            require_once('./DBInfo.php');
            $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql0 = 'SELECT roomno,customerID,orderID FROM reserve where date = "' .$ymd. '"';

            $st0 = $pdo->prepare($sql0);

            $st0->execute();

            // 当日分のデータベースのチェック
            while($row = $st0->fetch()){
                $resData[$re] = array($row[0],$row[1],$row[2],$row[3]);
                $res[$re] = $row[0];
                $re++;
            }

            // 予約の追加
            if($add != 0){
                foreach($addCol as $adc){
                    if(!in_array($adc[0],$res)){
                        $sql1 = 'INSERT INTO reserve SET date = ?, roomno = ?, customerID = ?, leader = ?, num = ?, price = ?';
                        if($conpileOrder == ''){
                            $sql1 .= ', orderID = ?';
                        }

                        $statement = $pdo->prepare($sql1);

                        $statement->bindValue(1, $ymd);
                        $statement->bindValue(2, $adc[0]);
                        $statement->bindValue(3, $customerID);
                        $statement->bindValue(4, $adc[3]);
                        $statement->bindValue(5, $adc[2]);
                        $statement->bindValue(6, $adc[4]);
                        if($conpileOrder == ''){
                            $statement->bindValue(7, $adc[1]);
                        }
  
                        $pdo->beginTransaction();
                
                        $statement->execute();
                        
                        $pdo->commit();
                        
                        $message[$me] = <<< EOA
【予約の追加】
部屋番号：{$adc[0]} 
代表者様：{$adc[3]} 様
人数：{$adc[2]} 名様

EOA;

                        $me++;
                    }
                }
            }

            // 予約の変更
            if($cha != 0){
                foreach($changeCol as $chc){
                    for($r=0;$r<$re;$r++){
                        if($chc[0] == $resData[$r][0] && $orderID == $resData[$r][2]){
                            echo $chc[0];
                            $sql2 = 'UPDATE reserve SET leader = ?, num = ? WHERE date = ? AND roomno = ?';

                            $st2 = $pdo->prepare($sql2);

                            $st2->bindValue(1, $chc[2]);
                            $st2->bindValue(2, $chc[1]);
                            $st2->bindValue(3, $ymd);
                            $st2->bindValue(4, $chc[0]);

                            $pdo->beginTransaction();
                
                            $st2->execute();
                            
                            $pdo->commit();
                            
                            $message[$me] = <<< EOC

【予約の変更】
部屋番号：{$chc[0]}
代表者様：{$chc[2]}  様
人数：{$adc[1]}名様

EOC;

                            $me++;
                        }
                    }                    
                }
            }

            // 予約のキャンセル
            $cancelOK = array();
            $co = 0;
            $cancelCost = 0;

            if($can != 0){
                foreach($cancelCol as $cac){
                    if(in_array($cac[0],$res)){
                        $sql3 = 'DELETE FROM reserve WHERE orderID = "' .$cac[1]. '" AND roomno = ' .$cac[0];
                        echo $sql3;
                        $pdo->beginTransaction();
                        $pdo->exec($sql3);
                        $pdo->commit();

                        $cancelOK[$co] = $cac[0];
                        $co++;

                        $cancelCost += $cac[2];

                        $message[$me] = <<< EOB

【予約のキャンセル】
部屋番号：{$cac[0]}

EOB;

                        $me++;
                    }
                }

                // cancel用のデータベースに登録
                $sql4 = 'SELECT * FROM cancel WHERE date = "' .$date. '"';
                $st4 = $pdo->query($sql4);
                $overlap = $st4->fetchall();

                $cancelID = $date. sprintf('%02d',count($overlap)+1);

                $sql5 = 'INSERT INTO cancel SET date = ?, customerID = ?, cancelID = ?, price = ?';

                $st5 = $pdo->prepare($sql5);

                $st5->bindValue(1, $ymd);
                $st5->bindValue(2, $customerID);
                $st5->bindValue(3, $cancelID);
                $st5->bindValue(4, $cancelCost);
        
                $pdo->beginTransaction();            
                $st5->execute();        
                $pdo->commit();
            }

            // IDをまとめる処理
            if(isset($_POST['cOrder'])){
                $sql6 = 'UPDATE reserve SET orderID = ? WHERE date = ? AND customerID = ?';

                $st6 = $pdo->prepare($sql6);

                echo $ymd;
                echo $customerID;

                $st6->bindValue(1, $conpileOrder);
                $st6->bindValue(2, $ymd);
                $st6->bindValue(3, $customerID);

                $pdo->beginTransaction();
                
                $st6->execute();
                
                $pdo->commit();

                $message[$me] = <<< EOD

                【注文番号の統合】
                注文番号：{$conpileOrder}
                
EOD;

            $me++;

            }

            $sql7 = 'SELECT email FROM member where customerID ="' .$customerID. '"';
            $st7 = $pdo->prepare($sql7);
            $st7->execute();
            $email = $st7->fetchColumn();

        }catch(PDOException $e){
                $title = 'データベースエラー';
                $body = '<p>データベース読み込みにエラーが発生しました</p>';
        }
    }else{
        $title = 'エラー';
    }

    $pdo = NULL;

    if($me>0){        

        $mailto = $email;

        //Return-Pathに指定するメールアドレス
	    $returnMail = 'info@hogehoge.com';

        $sname = '予約システム';
        $mail = 'info@hogehoge.com';
        $subject = '変更完了のお知らせ';
    
        $mailContent1 = <<< EOM1
        {$name}　様
        この度はご利用いただき、ありがとうございます。
        
        予約の変更を承りました。
        以下の通り、承りましたのでご確認下さい。
        

予約日：{$year}年{$month}月{$day}日
~~~~~~

EOM1;

$mailContent2 = <<< EOM2

追加料金：{$addPrice}円
変更料金：{$cancelCost}円

お客様のご来所を心よりお待ちしております。

======
予約システム
https://verse-straycat.ssl-lolipop.jp/fl/reserve/
EOM2;

        $body = $mailContent1;
        foreach ($message as $v) {
            $body .= $v;
        }
        $body .=$mailContent2;

        mb_language('ja');
        mb_internal_encoding('UTF-8');

        //Fromヘッダーを作成
        $header = 'From: ' . mb_encode_mimeheader($sname). ' <' . $mail. '>';

        if (mb_send_mail($mailto, $subject, $body, $header, '-f'. $returnMail)) {
            $msg = '予約の変更を承りました。<br>内容につきましては、メールをご確認ください。';
        
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
        echo $msg;
    }

?>

</div>
<div class="rm">
    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</body>
</html>