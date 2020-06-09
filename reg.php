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

    if(isset($_POST['lastname']) == true){
        $lastname = $_POST['lastname'];
        $lastname = htmlspecialchars($lastname);
    }else{
        $errorflg = true;
    }

    if(isset($_POST['firstname']) == true){
        $firstname = $_POST['firstname'];
        $firstname = htmlspecialchars($firstname);
    }else{
        $errorflg = true;
    }

    if(isset($_POST['email']) == true){
        $email = $_POST['email'];
    }else{
        $errorflg = true;
    }

    if(isset($_POST['pass']) == true){
        $pass = $_POST['pass'];
        $password = password_hash($pass, PASSWORD_DEFAULT);
    }else{
        $errorflg = true;
    }

    if(isset($_POST['sex']) == true){
        if($_POST['sex'] == '男性'){
            $sex = 1;
        }else if($_POST['sex'] == '女性'){
            $sex = 2;
        }else{
            $errorflg = true;
        }
    }else{
        $errorflg = true;
    }

    if(isset($_POST['job']) == true){
        $job = $_POST['job'];
    }else{
        $errorflg = true;
    }

    if(isset($_POST['zip01']) == true){
        $zip = $_POST['zip01'];
    }else{
        $errorflg = true;
    }

    if(isset($_POST['pref01']) == true){
        $pref = $_POST['pref01'];
        $pref = htmlspecialchars($pref);
    }else{
        $errorflg = true;
    }

    if(isset($_POST['addr01']) == true){
        $address = $_POST['addr01'];
        $address = htmlspecialchars($address);
    }else{
        $errorflg = true;
    }

    if($errorflg == true){
        $info = '登録のエラーが発生しました。お手数ですが再度ご登録お願いいたします。';
        goto end;
    }
/*

    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    $mailAdmin = 'hoge@hogehoge.com';
    $mailTitle = '投稿ありがとうございます';

    $mailContent = <<<EOM
{$name}　様
掲示板の書き込みありがとうございます！
記事を削除したい場合は、以下にアクセスして「削除パスワード」を入力してください。

====================

名前        ：　{$name}
タイトル    ：　{$title}
ジャンル    ：　{$genre}
削除パスワード  ：  セキュリティ保護のため表示されません
記事    ：
{$message}

====================

画像UP掲示板

EOM;

*/
    try{
        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);
                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);       
                        
        $sql = 'INSERT INTO member SET firstname = ?, lastname = ?, email = ?, password = ?, sex = ?, job = ?, zip = ?,pref = ?, address = ?, stay = -1';
        $statement = $pdo->prepare($sql);

        $statement->bindValue(1, $firstname);
        $statement->bindValue(2, $lastname);
        $statement->bindValue(3, $email);
        $statement->bindValue(4, $password);
        $statement->bindValue(5, $sex);
        $statement->bindValue(6, $job);
        $statement->bindValue(7, $zip);
        $statement->bindValue(8, $pref);
        $statement->bindValue(9, $address);

        $pdo->beginTransaction();
        
        $statement->execute();
        
        $pdo->commit();

        setcookie('name',$name,time()+ 60*60*24*7);
        setcookie('email',$email,time()+ 60*60*24*7);
        $info = '仮登録が完了しました。メールアドレスに記載されているアドレスをクリックすると、本登録が完了いたします。';
        setcookie('info',$info);

        /*
        // 確認メールの送信
        if(mb_send_mail($email,$mailTitle,$mailContent)){
            $info .= '<br>確認メールを送信しました';
        }else{
            $info .= '<br>確認メールが送信できませんでした';
        }

        */


    
    }catch(PDOException $e){
        if(isset($pdo) == true && $pdo->inTransaction() == true){
            $pdo->rollBack();
            $info = 'データベース読み込みエラー！';
            setcookie('info',$info);
        }
    }

    echo $info;

    end:
