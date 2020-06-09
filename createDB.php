<?php
    date_default_timezone_set('Asia/Tokyo');
    $myPage = basename($_SERVER['PHP_SELF']);
    $loginFlg = false;
    $name = '';
    
    $roomno = array('101','102','103','201','202','203');   // 部屋No

    $nYear = 2020;
    $nMonth = 1;
    $nDay = 1;
    $nflg = false;

    // 表示月１日の曜日を調べる
    $dFirstday = mktime(0, 0, 0, $dMonth, 1, $dYear);
    $dFirstweek = date('w', $dFirstday);

    // 表示される月の最終日を調べる    
    $dLastday = 28;
    while(checkdate($dMonth,$dLastday+1,$dYear)){
        $dLastday++;
    }
    
    try{

        require_once('./DBInfo.php');
        $pdo = new PDO(DBInfo::DSN, DBInfo::USER, DBInfo::PASSWORD);                        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           
	    $st1 = $pdo->prepare('SHOW TABLES LIKE "(table:)"');
		    $statement->bindValue(':table', $nYear, PDO::PARAM_STR);
            $statement->execute();

		    if($row = $statement->fetch()){
 
                $password_hash = $row[password];
 
			    //パスワードが一致
			    if (password_verify($password, $password_hash)) {
				
                    //セッションハイジャック対策
                    session_regenerate_id(true);
				
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $row['lastname'] . $row['firstname'] . '様';
                    $ftitle = 'ログインしました！';
                    $body = 'いらっしゃいませ。<br>ウィンドウを閉じてください';

	    		}else{
                    $errorFlg = true;
                    $body = 'パスワードが間違っています。<br>再度入力して下さい。';
		    	}
    		}else{
                $errorFlg = true;
	    		$body = 'IDまたはパスワードが間違っています。<br>再度入力して下さい。';
    		}
        }catch(PDOException $e){
            if(isset($pdo) == true && $pdo->inTransaction() == true){
                $errorFlg = true;
                $body = 'エラーが発生しました。<br>お手数ですが、再度やり直してください。';
            }
        }  
    }  	
