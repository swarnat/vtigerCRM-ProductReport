<?php
    $sql = "SELECT productid, productname FROM vtiger_products INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = productid AND deleted = 0)";
    $result = $adb->query($sql);


?>
<div style="background-color:#ffffea;border:1px solid #129456;width:250px;padding:20px;left:20px;position:relative;box-shadow:0px 0 2px #777777;">
    <img src='modules/ProductReport/icons/cross-button.png' style="position:absolute;right:-5px;top:-5px;cursor:pointer;" onclick='jQuery("#Wf2ListViewPOPUP").hide();'>
    <select id="filterProductSelector" style='width:250px;'>
        <option value="">Choose product</option>
        <?php while($row = $adb->fetchByAssoc($result)) {?>
            <option value='<?php echo $row["productid"] ?>' <?php if($_SESSION["ProductReport_ProductID"] == $row["productid"]) echo "selected='selected'"; ?>><?php echo $row["productname"] ?></option>
        <? } ?>
    </select><br>
    <div style='float:left;display:block;width:70px;line-height:22px;'>Date From:</div> <input type="text" style="width:80px;" name="pr_date_from" id="pr_date_from" value='<?php if(empty($_SESSION["ProductReport_DateFrom"])) { echo date("Y-m-d", time() - (86400 * 364)); } else { echo date("Y-m-d", strtotime($_SESSION["ProductReport_DateFrom"])); } ?>'>

    <img src="modules/Workflow2/icons/calenderButton.png" style="margin-bottom:-8px;cursor:pointer;" id="jscal_trigger_pr_date_from">
    <script type="text/javascript">Calendar.setup ({inputField : "pr_date_from", ifFormat : "%Y-%m-%d", button:"jscal_trigger_pr_date_from",showsTime : false, singleClick : true, step : 1});</script>
    <br>
    <div style='float:left;display:block;width:70px;line-height:22px;'>Date To:</div> <input type="text" style="width:80px;" name="pr_date_to" id="pr_date_to" value='<?php if(empty($_SESSION["ProductReport_DateTo"])) { echo date("Y-m-d"); } else { echo date("Y-m-d", strtotime($_SESSION["ProductReport_DateTo"])); } ?>'>
    <img src="modules/Workflow2/icons/calenderButton.png" style="margin-bottom:-8px;cursor:pointer;" id="jscal_trigger_pr_date_to">
    <script type="text/javascript">Calendar.setup ({inputField : "pr_date_to", ifFormat : "%Y-%m-%d", button:"jscal_trigger_pr_date_to",showsTime : false, singleClick : true, step : 1});</script>
    <br>

    <br>
    <input type="button" class="button edit small" value="start Filter" onclick='startFilter();'>
    <input type="button" class="button edit small" value="clear Filter" onclick='clearFilter();'>
    <br><br><br>
    <span style='font-size:11px;color:#999;font-weight:bold;'>Extension developed by <a href='http://vtiger.stefanwarnat.de' target="_blank" style='font-size:11px;color:#555;text-decoration:underline;font-weight:bold;'>Stefan Warnat</a>
    </span>
</div>