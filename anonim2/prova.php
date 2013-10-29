<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Documento senza titolo</title>
</head>

<body>
<?php
require_once("AVL.php");
$avl = new AVLTree();
$avl->retrieve("ciao");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("come");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("va?");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("brutto");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("scemo");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("che");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("non");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("non");
//$avl->display();
echo '<br /><br />';
$avl->retrieve("altro?");
//$avl->display();
echo '<br /><br />';
?>
</body>
</html>