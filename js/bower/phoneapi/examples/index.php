<html>
<head>
    <title>test</title>
    <script src="bower/jquery/dist/jquery.min.js"></script>
    <script src="bower/pusher/dist/web/pusher.js"></script>
    <script src="bower/vue/dist/vue.min.js"></script>
    <script src="bower/phoneapi/dist/js/pusher.js"></script>
</head>
<body>
<?php if (User::fromSession()->show_phone_calls) :?>
    <div class="phone-app">
        <? include 'bower/phoneapi/dist/template/_phone_api.php'; ?>
        <phone user_id="102" type="egerep" key="pusher_key"></phone>
    </div>
<?php endif ?>
</body>
</html>