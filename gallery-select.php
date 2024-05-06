<?php
// *************************************
// セッション変数
// *************************************
session_cache_limiter('nocache');
session_start();

// *************************************
// ブラウザに対する指示
// *************************************
header( "Content-Type: text/html; charset=utf-8" );

$images_dir = 'images';

$file_list = null;
if ($handle = opendir($images_dir)) {
  while (false !== ($entry = readdir($handle))) {
    if ( is_dir("$images_dir/$entry") ) {
      continue;
    }
    if ($entry != "." && $entry != "..") {
      $file_list[] = $entry;
      $file_time[] = filemtime("$images_dir/$entry");
    }
  }
  closedir($handle);

  if ( $file_list != null ) {
    array_multisort($file_time, SORT_DESC, $file_list);
  }

  $file_count = count($file_list);
}
else {
  $file_count = 0;
}

?>
<!DOCTYPE html>
<head>
<meta content="width=device-width initial-scale=1.0 minimum-scale=1.0 maximum-scale=1.0 user-scalable=no" name="viewport">
<meta charset="utf-8">
<title>ギャラリー</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

<style>
* {
  font-family: Arial, Helvetica, Verdana, "ヒラギノ角ゴPro W3", "Hiragino Kaku Gothic Pro", Osaka, "メイリオ", Meiryo, "ＭＳ Ｐゴシック", sans-serif!important;
}

#images {
  padding: 10px;
}

#images img {
  width: 100px;
  margin: 6px;
}

#head {
  background-color: #404040;
  padding: 10px 15px 10px 15px;
}
#head * {
  color: #ffffff;
}

/* PC 用の表示 */
@media screen and ( min-width:480px ) {
  #qrcode {
    margin-top: 200px!important;
  }
}

/* スマホ用の表示 */
@media screen and ( max-width:479px ) {
  #qrcode {
    display: none;
  }
}
</style>
<script>
  $(function(){

    $("#images img").each(function(index, obj){
      $(obj).on("click", function(){
        if ( $(this).css("opacity") == "0.2" ) {
          $(this).css("opacity", "1");
        }
        else {
          $(this).css("opacity", "0.2");
        }
      });
    });

    // 削除ボタン
    $("#act_button")
      // クリックした時の処理
      .on("click", function(){

        var target = 0;
        $("#images img").each(function(index, obj){
          if ( $(this).css("opacity") == "0.2" ) {
            target++;
          }
        });

        if ( target == 0 ) {
          alert("画像が選択されていません");
          return;
        }

        if (!confirm("選択した画像を処理しますか?")) {
          return;
        }

        // 新規送信用オブジェクト
        var formData = new FormData();

        $("#images img").each(function(index, obj){
          if ( $(this).css("opacity") == "0.2" ) {
            formData.append("images[]", (($(this).prop("title")).split(":"))[0] );
          }
        });

        // フィールド作成

        // **************************************
        // サーバ呼び出し
        // **************************************
        $.ajax({
          url: "action.php",
          type: "POST",
          data: formData,
          processData: false,  // jQuery がデータを処理しないよう指定
          contentType: false   // jQuery が contentType を設定しないよう指定
        })
        .done(function( data, textStatus ){
          console.log( "status:" + textStatus );
          console.log( "data:" + JSON.stringify(data, null, "    ") );

          alert("処理されました");
          location.reload(true);

        })
        .fail(function(jqXHR, textStatus, errorThrown ){
          console.log( "code:" + jqXHR.status );
          console.log( "status:" + textStatus );
          console.log( "errorThrown:" + errorThrown );

          // エラーメッセージを表示
          alert("システムエラーです");

        })
        .always(function() {
        })
        ;

    });

    // **************************************
    // このページ自身の QRコードの表示
    // **************************************
    $('#qrcode')
      .css({ "margin" : "20px 20px 20px 20px" })
      .qrcode({width: 160,height: 160,text: location.href });

  });
</script>
</head>
<body>
<div id="head">
  <div id="title">
    ギャラリー ( 処理画像選択 + Ajax 処理 )
  </div>
</div>

<div id="images">
<?php

// *************************************
// ファイル一覧
// *************************************

// ループ処理
for( $i = 0; $i < $file_count; $i++ ) {

  $entry = $file_list[$i];

  $type = image_type_to_mime_type( exif_imagetype( "{$images_dir}/{$entry}" ) );

  $cnt = $i + 1;

  print <<<GAZOU
<img src="{$images_dir}/{$entry}" title="{$entry}:{$type}" id="image{$cnt}" style='width:100px;'>

GAZOU;

}

?>

  
</div>
<div class="m-4">  
  <input type="button" id="act_button" value="実行" class="btn btn-primary">
</div>

  
<div id="qrcode"></div>

</body>
</html>
