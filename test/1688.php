<?php
include '../lib/ImageSearchProduct1688.php';

$image_url = 'https://cbu01.alicdn.com/img/ibank/2019/796/281/10643182697_146539942.220x220xz.jpg';
$p = new ImageSearchProduct1688();

$data = $p->getByUrl($image_url);
print_r($data);