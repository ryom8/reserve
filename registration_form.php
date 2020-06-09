<?php

    $errorFlg = false;
    $title = '登録失敗';
    $mail = '';

    session_start();
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
     
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    if(empty($_GET)) {
        header("Location: index.php");
        exit();
    }else{
        //GETデータを変数に入れる
        $urltoken = isset($_GET[urltoken]) ? $_GET[urltoken] : NULL;
        //メール入力判定
        if ($urltoken == ''){
            $errorFlg = true;
        }else{

    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare("SELECT mail FROM pre_member WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour");
        $statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
        $statement->execute();

        //レコード件数取得
		$row_count = $statement->rowCount();
			
		//24時間以内に仮登録され、本登録されていないトークンの場合
		if( $row_count ==1){
			$mail_array = $statement->fetch();
			$mail = $mail_array[mail];
            $_SESSION['mail'] = $mail;
            
            $title = 'ユーザー本登録';
		}else{
			$errorFlg = true;
		}
			
		//データベース接続切断
		$pdo = null;

    }catch(PDOException $e){
        if(isset($pdo) == true && $pdo->inTransaction() == true){
            $pdo->rollBack();
        }
    }
}
    }
?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <script type="text/javascript" src="register.js"></script>

	<title><?php echo $title;   ?></title>
</head>
<body>
    <header>
        <h1><?php echo $title;   ?></h1>
    </header>

<?php
    if($errorFlg == true){
        echo '<div class="center" id="register">';
        echo '<p>エラーが発生しました。お手数ですが、登録をやり直してください</p>';
        echo '<button id="l_top" class="button2">戻る</button>';
        echo '</div>';
    }else{

        echo <<< EOM
    <div class="center" id="register">
        <form action="reg_check.php" method="POST">
        <table class="reg">
            <tr class="reg">
                <td class="reg">お名前</td>
                <td class="reg">姓：<input type="text" name="lastname" required>　名：<input type="text" name="firstname" required></td>
            </tr>
            <tr class="reg">
                <td class="reg">パスワード</td>
                <td class="reg"><input type="password" name="pass" minlength="8" maxlength="16" required></td>
            </tr>
            <tr class="reg">
                <td class="reg">性別</td>
                <td class="reg"><select name="sex" class="reg2" required>
                    <option>男性</option>
                    <option>女性</option>
                </select> </td>
            </tr>
            <tr class="reg">
                <td class="reg">ご職業</td>
                <td class="reg"><select name="job" class="reg2" required>
                    <option>学生</option>
                    <option>公務員</option>
                    <option>会社員</option>
                    <option>主婦</option>
                    <option>その他</option>
                </select> </td>
            </tr>
            <tr class="reg">
                <td class="reg">郵便番号</td>
                <td class="reg"><input type="text" name="zip01" size="10" maxlength="8" onKeyUp="AjaxZip3.zip2addr(this,'','pref01','addr01');"></td>
            </tr>
            <tr class="reg">
                <td class="reg">都道府県</td>
                <td class="reg"><input type="text" name="pref01" size="20" maxlength="8"></td>
            </tr>
            <tr class="reg">
                <td class="reg">以降のご住所</td>
                <td class="reg"><input type="text" name="addr01" size="60"></td>
            </tr>
        </table>
        <input type="hidden" name="token" value="{$token}">
        <input type="hidden" name="email" value="{$mail}">
        <input type="submit" value="登録" id="regbutton" class="button2">
        <input type="reset" class="button2" value="リセット">
        <button id="l_top" class="button2">戻る</button>
        </form>
       
    </div>

EOM;

    }
    ?>
    <footer>
        <p>予約システム Ver.1.0</p>
    </footer>
</body>
</html>