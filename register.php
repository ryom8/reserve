<?php

    session_start();
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];

    header('X-FRAME-OPTIONS: SAMEORIGIN');

    ?>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<link href="index.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif+JP&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="index.js"></script>

	<title>ユーザー登録</title>
</head>
<body>
<header>
<h1>ユーザー登録</h1>
</header>
<div class="subwindow">
<form action="registration_mail_check.php" method="post">
<p>メールアドレス:　<input type="email" name="email" required></p>
<p>登録を押下後、メールが送信されます</p>

<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="submit" value="登録" class="button2">

</form>
</div>
</body>
</html>