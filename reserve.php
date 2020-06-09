<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');

    $loginFlg = false;
    $errorFlg = false;
    $resFlg = false;

    $title = '予約のご案内';
    $body = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $roomno2 = array('0101','0102','0103','0201','0202','0203');   // 部屋No
    $roomRes = '';
    $stays = array_fill(0,6,'〇');  // 部屋の空き情報の初期化   
    $empty = array_fill(0,6,'Empty');   // 部屋の空き情報を初期化

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
    }else{
        header('location:login.php');
    }

    if(isset($_GET['stay'])){
        $year = substr($_GET['stay'],0,4);
        $month = substr($_GET['stay'],5,2);
        $day = substr($_GET['stay'],8);        

        // 予約可能かどうかのチェック（当日より30日以内であれば登録可能）
        $now = strtotime(date('Y-m-d'));
        $target = strtotime($_GET['stay']);
        $days = ($target - $now) / (60*60*24);
        
        if($days >= 30){
            $errorFlg = true;
        }        

    }else{
        $errorFlg = true;
    }



    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql= 'SELECT roomno FROM reserve WHERE date ="' .$_GET['stay']. '"';

            $statement = $pdo->prepare($sql);

            $statement->execute();
            $r = 0;

            while($row = $statement->fetch()){
                $reserve[$r] = $row[0];
                for($t=0;$t<6;$t++){
                    if($reserve[$r] == $roomno[$t]){
                        $stays[$t] = '×';
                        $empty[$t] = 'noEmpty';
                        $resFlg = true;
                        $roomRes .= '<input type="hidden" name="' .$roomno2[$t]. '" value="×">';
                    }
                }
                $r++;
            }


    }catch(PDOException $e){
            $title = 'データベースエラー';
            $body = '<p>データベース読み込みにエラーが発生しました</p>';
    }

    if($errorFlg == true){
        $title = 'パラメータエラー';
        $body = '<p>不正なパラメータが入力されました。</p>';

    }

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="reserve.js"></script>

	<title>部屋のご予約</title>
</head>
<body>
<header>
<h1><?php echo $title; ?></h1>
</header>
<?php 
    echo '<p class="info">' .$month. '月' .$day. '日　客室情報</p>';
    ?>
<form action="reserve_ready.php" method="POST">
<input type="hidden" name="day" value="<?php echo $_GET['stay']; ?>">
<div class="subwindow rm">

    <?php

        if($errorFlg == false){
            if($resFlg == true){
                echo $roomRes;
            }
            for($d=0;$d<count($roomno);$d++){
                echo '<div class="rooms">';
                echo '<p class="roomno">' .$roomno[$d]. '</p>';
                echo '<br><button id="' .$roomno[$d]. '" class="' .$empty[$d]. '">' .$stays[$d]. '</button>';
                echo '</div>';
            }
        }else{
            echo $body;
        }
    ?>
</div>
<div class="rm">
    <button id="wReset" class="button2 bcenter" onClick="location.reload();">リセット</button>
    <input type="submit" id="submit" value="予約" class="button2">
    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</form>
</body>
</html>