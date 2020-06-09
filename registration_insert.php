<?php

    $errorFlg = false;
    $ftitle = '登録に失敗しました';
    $body = 'お手数ですが、登録をやり直してください。';

    session_start();
    if ($_POST['token'] != $_SESSION['token']){
        echo "不正アクセスの可能性あり";
        exit();
    }

    header('X-FRAME-OPTIONS: SAMEORIGIN');

    $firstname = $_SESSION['firstname'];
    $lastname = $_SESSION['lastname'];
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
    $sex = $_SESSION['sex'];
    $job = $_SESSION['job'];
    $zip = $_SESSION['zip'];
    $pref = $_SESSION['pref'];
    $address = $_SESSION['address'];

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        // アカウントの重複チェック
        $sq = 'SELECT * FROM member WHERE email ="' .$email. '"';
        $st = $pdo->query($sq);
        $idcheck = $st->fetchall();

        // 重複されていなければ、データベースに追加する
        if(count($idcheck) == 0){

            // 顧客IDを作成
            $st0 = $pdo->query('SELECT * FROM member');
            $st0->execute();
            $count = $st0->rowCount();
            echo 'a';
            $customerID =  'cstm'. sprintf('%04d',$count+1);
                        
            $sql = 'INSERT INTO member SET firstname = ?, lastname = ?, customerID = ?,email = ?, password = ?, sex = ?, job = ?, zip = ?,pref = ?, address = ?, stay = 0';
            $statement = $pdo->prepare($sql);

            $statement->bindValue(1, $firstname);
            $statement->bindValue(2, $lastname);
            $statement->bindValue(3, $customerID);
            $statement->bindValue(4, $email);
            $statement->bindValue(5, $password);
            $statement->bindValue(6, $sex);
            $statement->bindValue(7, $job);
            $statement->bindValue(8, $zip);
            $statement->bindValue(9, $pref);
            $statement->bindValue(10, $address);

            $pdo->beginTransaction();
            
            $statement->execute();
            
            $pdo->commit();

            $_SESSION = array();  
                
            //データベース接続切断
            $pdo = null;
            
            session_destroy();
            $ftitle = '本登録ができました';
            $body = 'ご登録ありがとうございました。<br>予約を行うには、トップページに戻ってください。';
        }else{
            $_SESSION = array();  
            $pdo = null;
            $body = '既に登録されています。<br>以前作成したメールアドレスにてログインを行ってください。';

        }

    }catch(PDOException $e){
        if(isset($pdo) == true && $pdo->inTransaction() == true){
            $pdo->rollBack();
            $info = 'データベース読み込みエラー！';
            setcookie('info',$info);
        }
    }    	
    
    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="register.js"></script>

	<title>ご登録ありがとうございました</title>
</head>
<body>
<header>
<h1><?php echo $ftitle; ?></h1>
</header>
<div class="subwindow">

<p><?php echo $body; ?></p>

<button id="l_top" class="button2">戻る</button>

</div>
</body>
</html>