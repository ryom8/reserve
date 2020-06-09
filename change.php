<?php

    session_start();
    session_regenerate_id(true);

    date_default_timezone_set('Asia/Tokyo');
    $today = date('Y-m-d');
    $now = strtotime($today);

    $loginFlg = false;
    $errorFlg = false;
    $resFlg = false;

    $title = '予約の変更・キャンセル';
    $body = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No
    $roomno2 = array('0101','0102','0103','0201','0202','0203');   // 部屋No
    $roomRes = '';
    $stays = array_fill(0,6,'〇');  // 部屋の空き情報の初期化   
    $empty = array_fill(0,6,'Empty');   // 部屋の空き情報を初期化
    $backID = '';

    if(isset($_SESSION['customerID'])){
        $customerID = $_SESSION['customerID'];
        $name = $_SESSION['name'];
    }else{
        header('location:login.php');
    }


    if($errorFlg == true){
        $title = 'パラメータエラー';
        $body = '<p>不正なパラメータが入力されました。</p>';

    }

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="change.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="change.js"></script>

	<title>予約の変更・キャンセル</title>
</head>
<body>
<header>
<h1><?php echo $title; ?></h1>
</header>
<?php 
    echo '<p>' .$name. '様ご予約一覧（本日～30日後）</p>';
    ?>

<div class="subwindow rm">

    <?php

try{
    require_once('./DBInfo.php');
    $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql= 'SELECT date,orderID,SUM(price) FROM reserve WHERE date >= "' .$today. '" AND customerID = "' .$customerID. '" GROUP BY orderID ORDER BY date ASC';

        $statement = $pdo->prepare($sql);

        $statement->execute();

        while($row = $statement->fetch()){
            if($backID == ''){
                echo '<table><thead><th>日付</th><th>注文番号</th><th>総金額</th><th>操作</th></thead>';
            }

            if($row[1] != $backID){

                $day = str_replace('-','/',$row[0]);
                $cc = strtotime($row[0]);
                $cd = ($cc -$now) / (60*60*24);
                $backID = $row[1];  // 同一注文番号のデータを重複して表示させないようにする

                if($cd > 4){
                    $cancelCost = 0;
                }else if($cd > 1){
                    $cancelCost = round($row[2] / 2);
                }else{
                    $cancelCost = $row[2];
                }

                echo '<tr><td>' .$day. '</td>';
                echo '<td>' .$row[1]. '</td>';
                echo '<td>￥' .$row[2]. '</td>';
                echo '<td><button id="' .$row[1]. '-ck" class="button check" value="' .$row[0]. '">確認</button><button id="' .$row[1]. '-ch" class="button change" value="' .$row[0]. '">変更</button><button id="' .$row[1]. '-ca" class="button cancel" value="' .$cancelCost. '">ｷｬﾝｾﾙ</button></td></tr>'; 
            }
        }

        if($backID != ''){
            echo '</table>';
        }else{
            echo '<p>本日以降の宿泊予定の情報はございません。</p>';
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