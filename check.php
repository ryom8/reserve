<?php

    session_start();
    session_regenerate_id(true);    

    $loginFlg = false;
    $errorFlg = false;
    $resFlg = false;

    $title = 'ご予約状況';
    $body = '';
    $price = 0;

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
        $name = $_SESSION['name'];
    }else{
        header('location:login.php');
    }

    if(isset($_GET['date'])){
        $date = str_replace('/','-',$_GET['date']);
        $year = substr($date,0,4);
        $month = substr($date,5,2);
        $day = substr($date,8,2);
    }else{
        $errorFlg = true;
    }

    if(isset($_GET['orderID'])){
        $orderID = substr($_GET['orderID'],0,8);
    }else{
        $errorFlg = true;
    }

    if($errorFlg == true){
        $title = 'パラメータエラー';
        $body = '<p>不正なパラメータが入力されました。</p>';

    }

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="check.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="change.js"></script>

	<title>予約の確認</title>
</head>
<body>
<header>
<h1><?php echo $title; ?></h1>
</header>
<?php 
    echo '<p>' .$year. '年　' .$month. '月　' .$day. '日　ご予約状況</p>';
    ?>

<div class="subwindow rm">

    <?php

try{
    require_once('./DBInfo.php');
    $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql= 'SELECT roomno,leader,num,orderID,price FROM reserve WHERE date = "' .$date. '" AND customerID = "' .$customerID. '" ORDER BY roomno ASC';

        $statement = $pdo->prepare($sql);

        $statement->execute();

        while($row = $statement->fetch()){
            if($price == 0){
                echo '<table><thead><th>部屋名</th><th>代表者様</th><th>人数</th><th>注文番号</th></thead>';
            }

            echo '<tr><td class="roomno">' .$row[0]. '</td>';
            echo '<td class="leader">' .$row[1]. '様</td>';
            echo '<td class="num">' .$row[2]. '</td>';
            echo '<td class="oid">';
            if($row[3] == $orderID){
                echo '<b>' .$row[3]. '</b>';
            }else{
                echo $row[3];
            }
            echo '</td>';
            $price += $row[4];
        }

        if($price != 0){
            echo '<tr><th colspan="3">合計金額</th><th class="oid">\\' .$price. '</th></tr>';
            echo '</table>';
        }else{
            echo '<p>本日の宿泊予定の情報はございません。</p>';
        }


}catch(PDOException $e){
        $title = 'データベースエラー';
        $body = '<p>データベース読み込みにエラーが発生しました</p>';
}

$pdo = NULL;


    ?>
</div>
<div class="rm">
    <button id="wclose" class="button2 bcenter">閉じる</button>
</div>
</body>
</html>