<?php

    $errorflg = false;
    $info = '';

    $lastname = '';
    $firstname = '';
    $email = '';
    $password = '';
    $sex = '';
    $job = '';
    $zip = '';
    $pref = '';
    $address = '';

    session_start();

    //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
    if ($_POST['token'] != $_SESSION['token']){
	echo "不正アクセスの可能性あり";
	exit();
}

    header('X-FRAME-OPTIONS: SAMEORIGIN');    

    if(empty($_POST)) {
        header("Location: registration_mail_form.php");
        exit();
    }else{
        //POSTされたデータを各変数に入れる

        if(isset($_POST['lastname']) == true){
            $lastname = $_POST['lastname'];
            $lastname = htmlspecialchars($lastname);
            $_SESSION['lastname'] = $lastname;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['firstname']) == true){
            $firstname = $_POST['firstname'];
            $firstname = htmlspecialchars($firstname);
            $_SESSION['firstname'] = $firstname;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['email']) == true){
            $email = $_POST['email'];
            $_SESSION['email'] = $email;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['pass']) == true){
            $pass = $_POST['pass'];
            $pass_hide = str_repeat('*',strlen($pass));
            $_SESSION['password'] = password_hash($pass, PASSWORD_DEFAULT);
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['sex']) == true){
            $sex = $_POST['sex'];
            if($_POST['sex'] == '男性'){
                $_SESSION['sex'] = 1;
            }else if($_POST['sex'] == '女性'){
                $_SESSION['sex'] = 2;
            }else{
                $errorflg = true;
            }
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['job']) == true){
            $job = $_POST['job'];
            $_SESSION['job'] = $job;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['zip01']) == true){
            $zip = $_POST['zip01'];
            $_SESSION['zip'] = $zip;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['pref01']) == true){
            $pref = $_POST['pref01'];
            $pref = htmlspecialchars($pref);
            $_SESSION['pref'] = $pref;
        }else{
            $errorflg = true;
        }
    
        if(isset($_POST['addr01']) == true){
            $address = $_POST['addr01'];
            $address = htmlspecialchars($address);
            $_SESSION['address'] = $address;
        }else{
            $errorflg = true;
        }

        $token = $_POST['token'];
    }

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="index.js"></script>

	<title>ユーザー登録確認</title>
</head>
<body>
<header>
<h1>内容確認</h1>
</header>

<?php
    if($errorFlg == true){
        echo '<p>エラーが発生しました。お手数ですが、登録をやり直してください</p>';
        echo '<button id="l_top" class="button2">戻る</button>';

    }else{
        echo <<<EOF

<div class="center" id="register">
        <form action="registration_insert.php" method="POST">
        <table class="reg">
            <tr class="reg">
                <td class="reg">お名前</td>
                <td class="reg">姓：{$lastname}　名：{$firstname} </td>
            </tr>
            <tr class="reg">
                <td class="reg">パスワード</td>
                <td class="reg">{$pass_hide}　※非表示</td>
            </tr>
            <tr class="reg">
                <td class="reg">性別</td>
                <td class="reg">{$sex} </td>
            </tr>
            <tr class="reg">
                <td class="reg">ご職業</td>
                <td class="reg">{$job} </td>
            </tr>
            <tr class="reg">
                <td class="reg">郵便番号</td>
                <td class="reg">{$zip}</td>
            </tr>
            <tr class="reg">
                <td class="reg">都道府県</td>
                <td class="reg">{$pref}</td>
            </tr>
            <tr class="reg">
                <td class="reg">以降のご住所</td>
                <td class="reg">{$address}</td>
            </tr>
        </table>
        <p>上記の内容で登録します。</p>
        <input type="submit" value="登録" id="regbutton" class="button2">
        <input type="hidden" name="token" value="{$token}">
        <input type="button" value="戻る" class="button2" onClick="history.back()">
        </form>
       
    </div>

EOF;

    }

?>
</body>
</html>