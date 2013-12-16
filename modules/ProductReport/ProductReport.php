<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 05.02.13
 * Time: 11:23
 */
class ProductReport
{
    const VERSION = "1.0";

	public function checkDB() {
		global $adb;
        require_once("VtUtils.php");
		
	}
    public static function activateEvents() {
        #if(vtlib_isModuleActive("SWEventHandler")) {
            global $adb;
            $em = new VTEventsManager($adb);

            // Registering event for Recurring Invoices
            $em->registerHandler('vtiger.filter.listview.querygenerator.query', 'modules/ProductReport/ReporterEventHandler.php', 'ReporterEventHandler', "");
            $em->registerHandler('vtiger.filter.listview.header', 'modules/ProductReport/ReporterEventHandler.php', 'ReporterEventHandler', "");
#            $em->registerHandler('vtiger.filter.listview.querygenerator.after', 'modules/ProductReport/ReporterEventHandler.php', 'ReporterEventHandler', "");
            $em->registerHandler('vtiger.filter.listview.render', 'modules/ProductReport/ReporterEventHandler.php', 'ReporterEventHandler', "");
        #}
    }

    public static function removeHeaderLink() {
        global $adb;

        $sql = "DELETE FROM vtiger_links WHERE linktype = 'HEADERSCRIPT' AND linklabel = 'ProductReportJS'";
        $adb->query($sql);

    }

	public static function initButton() {
        require_once('vtlib/Vtiger/Module.php');

        self::removeHeaderLink();

        $link_module = Vtiger_Module::getInstance("ProductReport");
        $link_module->addLink('HEADERSCRIPT','ProductReportJS','modules/ProductReport/js/frontend.js?v='.self::VERSION, "", "1");

        $module = Vtiger_Module::getInstance("Accounts");
        $module->addLink('LISTVIEWBASIC','Product Filter','filterProduct(this, \'$MODULE$\')');
    }

	public function vtlib_handler($modulename, $event_type) {
		global $adb;
		self::activateEvents();
        self::initButton();

		if($event_type == 'module.postinstall') {
			// TODO Handle actions when this module is disabled.			
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {

		}
	}

}
