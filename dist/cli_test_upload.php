$url = "http://localhost/final_admin_panel/dist/upload_test.php";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$cf = new CURLFile("C:/wamp64/www/final_admin_panel/dist/assets/img/user1-128x128.jpg", 'image/jpeg', 'test.jpg');
curl_setopt($ch, CURLOPT_POSTFIELDS, ['test' => $cf]);
$res = curl_exec($ch);
echo "Result: " . $res . "\n";
