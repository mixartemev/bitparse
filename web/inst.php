<?php
require_once 'Instagram.php';
$i = new Instagram('tmtsignal','Lenadima137513selyT', true);
//$i->login();
$uploaded = $i->uploadPhoto('coin.jpg', 'CryptoRate - ' . date('d.m.Y'). "


#bitcoin #crypto #ico #maining #биткоин #криптовалюта #блокчейн #invest #money #coin #blockchain #cryptocurrency
");
if(!$uid = $uploaded['upload_id']){
	print_r($uploaded);
}else{
	$s = $i->mediaToStory($uid, 'Cool story!');
	print_r($s);
}
