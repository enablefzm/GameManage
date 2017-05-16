<?php
require_once 'core.php';
$year = "2099";
$month = "05";
if (ob_session::CheckIsLogin()) {
    if (isset($_GET['y']))
        $year = $_GET['y'];

    if (isset($_GET['m']))
        $month = $_GET['m'];
}
$obExcel = new ob_excel($year, $month);
$obExcel->showExcel();
?>
