<?php

    $email = '';
    $errorFlg = false;
    $bd = 'エラーが発生しました。お手数ですが、再度登録お願いいたします。';
    $ftitle = 'エラー';

    session_start();
    if ($_POST['token'] != $_SESSION['token']){
        echo "不正アクセスの可能性あり";
        exit();
    }

    header('X-FRAME-OPTIONS: SAMEORIGIN');

    if(isset($_POST['email']) == true){
        $email = $_POST['email'];
    }else{
        $errorFlg = true;
    }

    $urltoken = hash('sha256',uniqid(rand(),1));
    $url = 'https://twinklesky.biz/fl/reserve/registration_form.php' . '?urltoken=' .$urltoken;

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare("INSERT INTO pre_member (urltoken,mail,date) VALUES (:urltoken,:mail,now() )");
		
		//プレースホルダへ実際の値を設定する
		$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
		$statement->bindValue(':mail', $email, PDO::PARAM_STR);
		$statement->execute();
			
		//データベース接続切断
        $pdo = null;
        
        $bd = '仮登録が完了しました。<br>24時間以内に、メールのURLより本登録を行ってください。';

    }catch(PDOException $e){
        if(isset($pdo) == true && $pdo->inTransaction() == true){
            $pdo->rollBack();
            $info = 'データベース読み込みエラー！';
            setcookie('info',$info);
        }
    }

    	//メールの宛先
	$mailTo = $email;
 
	//Return-Pathに指定するメールアドレス
	$returnMail = 'info@hogehoge.com';
 
	$name = "予約システム";
	$mail = 'info@hogehoge.com';
	$subject = "予約システム　本登録のお知らせ";
 
$body = <<< EOM
ご登録ありがとうございます。
本登録は24時間以内に、以下のURLで登録を済ませてください。
{$url}
EOM;

 
	mb_language('ja');
	mb_internal_encoding('UTF-8');
 
	//Fromヘッダーを作成
	$header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
 
	if (mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail)) {
	
	 	//セッション変数を全て解除
		$_SESSION = array();
	
		//クッキーの削除
		if (isset($_COOKIE["PHPSESSID"])) {
			setcookie("PHPSESSID", '', time() - 1800, '/');
		}
	
 		//セッションを破棄する
 		session_destroy();
 	
        $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
        $ftitle = 'メール送信のお知らせ';
 	
	 } else {
		$errors['mail_error'] = "メールの送信に失敗しました。";
    }
    
    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="index.js"></script>

	<title>ご登録ありがとうございました</title>
</head>
<body>
<header>
<h1><?php echo $ftitle; ?></h1>
</header>
<div class="subwindow">

<p><?php echo $bd; ?></p>

<button id="windowclose" class="button2">閉じる</button>

</div>
</body>
</html>