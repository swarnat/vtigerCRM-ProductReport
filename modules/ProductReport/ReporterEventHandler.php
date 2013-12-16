<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Colorizer and must not be distributsed without complete extension
**/
require_once('include/utils/utils.php');
require_once('ProductReport.php');
global $ReporterEventData;

class ReporterEventHandler extends VTEventHandler {

    /**
     * @param $handlerType
     * @param $entityData VTEntityData
     */
    public function handleEvent($handlerType, $entityData){

    }
    public function handleFilter($handlerType, $parameter) {
        global $ReporterEventData;
        global $currentModule;

        switch($handlerType) {
            case "vtiger.filter.listview.querygenerator.query":
                if($currentModule == "Accounts" && !empty($_SESSION["ProductReport_ProductID"])) {
                    $query = preg_match("/SELECT(.*?)FROM(.*)WHERE(.*)/", $parameter, $matches);

                    $matches[1] = "DISTINCT COUNT(*) as numrows, ".$matches[1];

                    $matches[2] .= " INNER JOIN vtiger_invoice ON(vtiger_invoice.accountid = vtiger_account.accountid)";
                    $matches[2] .= " INNER JOIN vtiger_crmentity `invoiceCRMentity` ON(invoiceCRMentity.crmid = vtiger_invoice.invoiceid AND invoiceCRMentity.createdtime >= '".$_SESSION["ProductReport_DateFrom"]."' AND invoiceCRMentity.createdtime <= '".$_SESSION["ProductReport_DateTo"]."')";
                    $matches[2] .= " INNER JOIN vtiger_inventoryproductrel ON(vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid AND vtiger_inventoryproductrel.productid = ".$_SESSION["ProductReport_ProductID"].")";

                    $matches[3] .= " GROUP BY vtiger_invoice.accountid";
                    $parameter = "SELECT ".trim($matches[1])." FROM ".trim($matches[2])." WHERE ".trim($matches[3]);
                }

                break;
            case "vtiger.filter.listview.header":
                if($currentModule == "Accounts" && !empty($_SESSION["ProductReport_ProductID"])) {
                    $parameter[] = "found Invoices";
                }
                break;
            case "vtiger.filter.listview.render":
                if($currentModule == "Accounts" && !empty($_SESSION["ProductReport_ProductID"])) {
                    $parameter[0][] = $parameter[1]["numrows"];
                }

                break;
        }

        return $parameter;
    }
}
