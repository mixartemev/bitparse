<?php
require_once 'Instagram.php';
$i = new Instagram('tmtsignal','Lenadima137513selyT', true);
//$i->login();
$i->uploadPhoto('coin.jpg', 'CryptoRate');
