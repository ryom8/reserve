<?php
    session_start();
    session_regenerate_id(true);

    $errorflg = false;

    $orderID = '';
    $cancelID = '';
    $date = '';
    $price = 0;
    $email = '';

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
        $name = $_SESSION['name'];
    }else{
        $errorflg = true;
    }

    if(isset($_POST['id'])){
        $orderID = substr($_POST['id'],0,8);
    }else{
        $errorflg = true;
    }

    if(isset($_POST['price'])){
        $price = $_POST['price'];

    }else{
        $errorflg = true;
    }

    // エラー時はCookieにエラーログを書き込む
    if($errorflg == true){
        $info = 'エラー！予約キャンセルに失敗しました。';
        goto end;
    }


    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // IDをデータベースより照合
        $sql1 = 'SELECT date FROM reserve WHERE orderID = "' .$orderID. '"';
        $st1 = $pdo->prepare($sql1);
        $st1->execute();
        $date = str_replace('-','',$st1->fetchColumn());

        $year = substr($date,0,4);
        $month = substr($date,4,2);
        $day = substr($date,6,2);

        // cancel用のデータベースに登録
        $sql2 = 'SELECT * FROM cancel WHERE date = "' .$date. '"';
        $st2 = $pdo->query($sql2);
        $overlap = $st2->fetchall();

        $cancelID = $date. sprintf('%02d',count($overlap)+1);

        $sql3 = 'INSERT INTO cancel SET date = ?, customerID = ?, cancelID = ?, price = ?';

        $st3 = $pdo->prepare($sql3);

        $st3->bindValue(1, $date);
        $st3->bindValue(2, $customerID);
        $st3->bindValue(3, $cancelID);
        $st3->bindValue(4, $price);

        $pdo->beginTransaction();            
        $st3->execute();        
        $pdo->commit();

        // 予約情報をreserveデータベースから削除

        $sql4 = 'DELETE FROM reserve WHERE orderID = "' .$orderID. '"';

        $pdo->beginTransaction();
        $pdo->exec($sql4);
        $pdo->commit();

        $sql5 = 'SELECT email FROM member where customerID ="' .$customerID. '"';
        $st5 = $pdo->prepare($sql5);
        $st5->execute();
        $email = $st5->fetchColumn();

        $mailto = $email;

        //Return-Pathに指定するメールアドレス
	    $returnMail = 'info@hogehoge.com';

        $sname = '予約システム';
        $mail = 'info@hogehoge.com';
        $subject = '予約キャンセル完了のお知らせ';
    
        $body = <<< EOM
{$name}　様
この度はご利用いただき、ありがとうございます。

予約のキャンセルを承りました。
以下の予約をキャンセルいたしましたのでお知らせいたします。

予約番号：{$orderID}
予約日：{$year}年{$month}月{$day}日
キャンセル番号：{$cancelID}

キャンセル料金：{$price}円

お客様のまたのご利用を心よりお待ちしております。

======
予約システム
https://twinklesky.biz/fl/reserve/
EOM;

        mb_language('ja');
        mb_internal_encoding('UTF-8');

        //Fromヘッダーを作成
        $header = 'From: ' . mb_encode_mimeheader($sname). ' <' . $mail. '>';

        mb_send_mail($mailto, $subject, $body, $header, '-f'. $returnMail);

        $info = '予約のキャンセルが完了しました。\n内容につきましては、メールをご確認ください';
    
    }catch(PDOException $e){
        if(isset($pdo) == true && $pdo->inTransaction() == true){
            $pdo->rollBack();
            $info .= 'データベース読み込みエラー！';
            setcookie('info',$info);
        }
    }
    end:

    echo $info;
