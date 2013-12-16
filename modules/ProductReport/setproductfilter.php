<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.12.13 18:06
 * You must not use this file without permission.
 */

if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $_POST["date_from"])) {
    $_POST["date_from"] = date("Y-m-d", time() - (86400 * 364));
}

if(!preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $_POST["date_to"])) {
    $_POST["date_to"] = date("Y-m-d");
}

$_SESSION["ProductReport_ProductID"] = intval($_POST["productid"]);
$_SESSION["ProductReport_DateFrom"] = ($_POST["date_from"]);
$_SESSION["ProductReport_DateTo"] = ($_POST["date_to"]);