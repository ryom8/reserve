<?php
    date_default_timezone_set('Asia/Tokyo');
    $myPage = basename($_SERVER['PHP_SELF']);
    $loginFlg = false;
    $name = '';

    $roomno = array('101','102','103','201','202','203');   // 部屋No

    // ログイン状況の確認
    session_start();
    session_regenerate_id(true);
    if(isset($_SESSION['name'])){
        $name = $_SESSION['name']. '様';
        $loginFlg = true;
        $bClass = 'nLogin';
    }else{
        $bClass = 'nLogoff';
    }

    // 現在の日付を取得
    $nYear = date('Y');
    $nMonth = date('m');
    $nDay = date('d');
    $nflg = false;

    if(isset($_GET['year'])){
        $dYear = $_GET['year'];
    }else{
        $dYear = $nYear;
    }

    if(isset($_GET['month'])){
        $dMonth = $_GET['month'];
    }else{
        $dMonth = $nMonth;
    }

    if($dYear == $nYear && $dMonth == $nMonth){
        $nflg = true;
    }

    // 表示月１日の曜日を調べる
    $dFirstday = mktime(0, 0, 0, $dMonth, 1, $dYear);
    $dFirstweek = date('w', $dFirstday);

    // 表示される月の最終日を調べる    
    $dLastday = 28;
    while(checkdate($dMonth,$dLastday+1,$dYear)){
        $dLastday++;
    }
    
    $pastYear = date('Y',strtotime('-1 month',$dFirstday));
    $pastMonth = date('m',strtotime('-1 month',$dFirstday));

    $nextYear = date('Y',strtotime('+1 month',$dFirstday));
    $nextMonth = date('m',strtotime('+1 month',$dFirstday));

    $days = days($nYear,$nMonth,$nDay);
    $stays = array_fill(0,30,'◎');

    // 30日後の日時を算出
    $l30y = substr($days[0][29],0,4);
    $l30m = substr($days[0][29],5,2);
    $l30d = substr($days[0][29],8);

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql= 'SELECT date,count(*) as count from reserve WHERE date >= "' .$days[0][0]. '" group by date order by date';

            $statement = $pdo->prepare($sql);

            $statement->execute();
            $r = 0;

            while($row = $statement->fetch()){
                $res[$r] = $row[0];
                $sta[$r] = $row[1];
                $r++;
            }

            if($r != 0){

                for($t=0,$u=0;$t<30 && $u < $r;$t++){

                    if($res[$u] == $days[0][$t]){
                        if($sta[$u] ==6){
                            $stays[$t] = '×';
                        }else if($sta[$u] >= 4){
                            $stays[$t] = '△';
                        }else{
                            $stays[$t] = '〇';
                        }
                        $u++;
                    }

                }

            }

    }catch(PDOException $e){
            echo 'データベースエラー';
    }

    $pdo = null;
    
    // 該当日のSQL用文字列と金額算出用
    function days($yy,$mm,$dd){
        $d = array();
        $week = array();
        $pr = array();        
        for($i=0;$i<30;$i++){
            $d[$i] = $yy. '-' .sprintf('%02d',$mm). '-' . sprintf('%02d',$dd);
            $week[$i] = date('w', mktime(0, 0, 0, $mm, $dd, $yy));

            // 金額の算出の判定（週末であるか、大型連休であるか）
            if($week[$i] >= 5){
                $pr[$i] = 1;
            }else{
                $pr[$i] = 0;
            }
            if(spDays($mm,$dd) == true){
                $pr[$i] = 2;
            }

            list($yy,$mm,$dd) = nextDay($yy,$mm,$dd);
        }
        return array($d,$pr);
    }

    // 次の日の日時を出力する
    function nextDay($y,$m,$d){
        if(!checkdate($m,$d+1,$y)){
            if($m == 12){
                $y++;
                $m = 1;
            }else{
                $m++;
            }
            $d = 1;
        }else{
            $d++;
        }
        return array($y,$m,$d);
    }

    // 大型連休時であるかの判定を行う（年末年始、GW、盆）
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

<!document html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="index.js"></script>
    <script src="js/lightbox.min.js"></script>

	<title>予約システム</title>
</head>
<body>
    <header>
        <h1><a href="<?php echo $myPage; ?>">予約システム</a></h1>
    </header>

    <div class="center" id="members">
        <?php
            if($loginFlg == true){
                echo '<p class="welcome">' .$name. '　いらっしゃいませ';
                echo '<button id="l_logout" class="button">ログアウト</button>';
                echo '<button id="l_howto" class="button">当サイトについて</button>';
                echo '<button id="l_change" class="button">変更・キャンセル</button>';
            }else{
                echo '<button id="l_new" class="button">新規登録</button>';                
                echo '<button id="l_login" class="button">ログイン</button>';
                echo '<button id="l_howto" class="button">当サイトについて</button>';
            }
            ?>
    </div>

    <div class="center" id="calender">



        <table class="calender">
            <caption><?php echo $dYear. ' 年 ' .$dMonth; ?> 月予約状況</caption>
            <tr>
                <thead>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</td>
                    <th>Sat</th>
                </thead>
            </tr>

        <?php

        $fd = 1 - $dFirstweek;

        // カレンダーの表示
        while($fd <= $dLastday){
            echo '<tr>';
            for($w=0;$w<=6;$w++){
                echo '<td>';
                if($nflg == true && $fd == $nDay){
                    echo '<b>' .$fd. '</b><br>'.$stays[0];

                }else if($fd > 0 && $fd <= $dLastday){
                    echo $fd;
                    if($dYear == $nYear || $dYear == $l30y){
                        $k = 1;
                        $ymd = $dYear. '-' .$dMonth. '-' .sprintf('%02d',$fd);
                        while($k < 30){
                            if($ymd == $days[0][$k]){
                                if($stays[$k] == '×'){
                                    echo '<br><b>' .$stays[$k]. '</b>';
                                }else{
                                    echo '<br><button id="' .$ymd. '" class="' .$bClass. '">' .$stays[$k]. '</button>';
                                    break;
                                }
                            }
                            $k++;
                        }
                    }
                }else{
                    echo '-';
                }
                echo '</td>';            
            $fd++;
            }
            echo '</tr>';
        }

        ?>
        </table>
        <div id="change">
            <button id="prevMonth" class="button" value="<?php echo $pastYear.$pastMonth; ?>">＜＜　<?php echo $pastYear. '年' .$pastMonth. '月'; ?></button>
            <button id="nextMonth" class="button" value="<?php echo $nextYear.$nextMonth; ?>"><?php echo $nextYear. '年' .$nextMonth. '月'; ?>　＞＞</button>

        </div>
       
    </div>

    <footer>
        <p>予約システム Ver.1.0</p>
    </footer>
</body>
</html>