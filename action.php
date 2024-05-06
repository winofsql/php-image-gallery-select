<?php
// *************************************
// セッション変数
// *************************************
session_cache_limiter('nocache');
session_start();

// *************************************
// ブラウザに対する指示
// *************************************
header( "Content-Type: application/json; charset=utf-8" );

print json_encode($_POST);

