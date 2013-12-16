<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
 * @version 1.2
 * @updated 2013-06-23
**/
if(!class_exists("VtUtils")) {

class VtUtils
{
    public static function getMandatoryFields($tabid) {
        global $adb;

        $sql = "SELECT * FROM vtiger_field WHERE tabid = ".intval($tabid)." AND typeofdata LIKE '%~M%'";
        $result = $adb->query($sql);

        $mandatoryFields = array();
        while($row = $adb->fetchByAssoc($result)) {
            $typeofData = explode("~", $row["typeofdata"]);

            if($typeofData[1] == "M") {
                $mandatoryFields[] = $row;
            }
        }

        return $mandatoryFields;
    }
    public static function getFieldsForModule($module_name, $uitype = false) {
        global $current_language;

        if($uitype !== false && !is_array($uitype)) {
            $uitype = array($uitype);
        }

        // Fields in this module
        include_once("vtlib/Vtiger/Module.php");

       	#$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
       	#foreach($alle as $datei) { include $datei; }

       	$module = $module_name;
       	$instance = Vtiger_Module::getInstance($module);
       	$blocks = Vtiger_Block::getAllForModule($instance);

        if($module != "Events") {
       	    $modLang = return_module_language($current_language, $module);
        }
        $moduleFields = array();

        if(is_array($blocks)) {
            foreach($blocks as $block) {
                $fields = Vtiger_Field::getAllForBlock($block, $instance);

                if(empty($fields) || !is_array($fields)) {
                    continue;
                }
                foreach($fields as $field) {
                    $field->label = (isset($modLang[$field->label])?$modLang[$field->label]:$field->label);
                    if($uitype !== false) {
                        if(in_array($field->uitype, $uitype)) {
                            $moduleFields[] = $field;
                        }
                    } else {
                        $moduleFields[] = $field;
                    }

                }
            }
        }

        return $moduleFields;
    }
    public static function getReferenceFieldsForModule($module_name) {
        global $adb;
        $relations = array();

        $sql = "SELECT tabid, fieldname, fieldlabel FROM vtiger_field WHERE tabid = ".getTabID($module_name)." AND (uitype = 10 OR uitype = 51 OR uitype = 52 OR uitype = 53 OR uitype = 57 OR uitype = 58 OR uitype = 59 OR uitype = 73 OR uitype = 75 OR uitype = 76 OR uitype = 78 OR uitype = 80 OR uitype = 81 OR uitype = 68)";
        $result = $adb->query($sql);

        while($row = $adb->fetchByAssoc($result)) {
            $row["module"] = self::getModuleName($row["tabid"]);
            $relations[] = $row;
        }

        return $relations;
   	}
    public static function getFieldsWithBlocksForModule($module_name, $references = false, $refTemplate = "([source]: ([module]) [destination])") {
        global $current_language, $adb, $app_strings;

        if(empty($refTemplate) && $references == true) {
            $refTemplate = "([source]: ([module]) [destination])";
        }

        // Fields in this module
        include_once("vtlib/Vtiger/Module.php");

       	#$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
       	#foreach($alle as $datei) { include $datei;		 }

       	$module = $module_name;
       	$instance = Vtiger_Module::getInstance($module);
       	$blocks = Vtiger_Block::getAllForModule($instance);

        if($module != "Events") {
            $langModule = $module;
        } else {
            $langModule = "Calendar";
        }
        $modLang = return_module_language($current_language, $langModule);

        $moduleFields = array();

        $addReferences = array();

        if(is_array($blocks)) {
            foreach($blocks as $block) {
                $fields = Vtiger_Field::getAllForBlock($block, $instance);

                if(empty($fields) || !is_array($fields)) {
                    continue;
                }

                foreach($fields as $field) {
                    $field->label = (isset($modLang[$field->label])?$modLang[$field->label]:$field->label);

                    if($references !== false) {

                        switch ($field->uitype) {
                            case "51":
                                   $addReferences[] = array($field,"Accounts");
                            break;
                            case "52":
                                   $addReferences[] = array($field,"Users");
                            break;
                            case "53":
                                   $addReferences[] = array($field,"Users");
                            break;
                            case "57":
                                   $addReferences[] = array($field,"Contacts");
                               break;
                            case "58":
                                   $addReferences[] = array($field,"Campaigns");
                               break;
                            case "59":
                                   $addReferences[] = array($field,"Products");
                               break;
                            case "73":
                                   $addReferences[] = array($field,"Accounts");
                               break;
                            case "75":
                                   $addReferences[] = array($field,"Vendors");
                               break;
                            case "81":
                                   $addReferences[] = array($field,"Vendors");
                               break;
                            case "76":
                                   $addReferences[] = array($field,"Potentials");
                               break;
                            case "78":
                                   $addReferences[] = array($field,"Quotes");
                               break;
                            case "80":
                                   $addReferences[] = array($field,"SalesOrder");
                               break;
                            case "68":
                                   $addReferences[] = array($field,"Accounts");
                                   $addReferences[] = array($field,"Contacts");
                                   break;
                            case "10": # Possibly multiple relations
                                    $result = $adb->pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', array($field->id));
                                    while ($data = $adb->fetch_array($result)) {
                                        $addReferences[] = array($field,$data["relmodule"]);
                                    }
                                break;
                        }
                    }

                    $moduleFields[getTranslatedString($block->label, $langModule)][] = $field;
                }
            }
        }

        $rewriteFields = array(
            "assigned_user_id" => "smownerid"
        );
        if($references !== false) {
            $field = new StdClass();
            $field->name = "current_user";
            $field->label = getTranslatedString("LBL_CURRENT_USER", "Workflow2");
            $addReferences[] = array($field, "Users");
        }
        if(is_array($addReferences)) {

            foreach($addReferences as $refField) {
    #            var_dump($refField);
                $fields = self::getFieldsForModule($refField[1]);

                foreach($fields as $field) {
                    $field->label = "(".(isset($app_strings[$refField[1]])?$app_strings[$refField[1]]:$refField[1]).") ".$field->label;

                    if(!empty($rewriteFields[$refField[0]->name])) {
                        $refField[0]->name = $rewriteFields[$refField[0]->name];
                    }
                    $name = str_replace(array("[source]", "[module]", "[destination]"), array($refField[0]->name, $refField[1], $field->name), $refTemplate);
                    $field->name = $name;

                    $moduleFields["References (".$refField[0]->label.")"][] = $field;
                }
            }
        }

        return $moduleFields;
    }

    public static function getAdminUser() {
        return Users::getActiveAdminUser();
    }

    public static function getEntityModules($sorted = false) {
        global $adb;
        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = $adb->query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            $module[$row["tabid"]] = array($row["name"], getTranslatedString($row["tablabel"], $row["name"]));
        }
        if($sorted == true) {
            asort($module);
        }

        return $module;
    }
    public static function getRelatedModules($module_name) {
        global $adb, $current_user, $app_strings;

        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        $sql = "SELECT vtiger_relatedlists.related_tabid,vtiger_relatedlists.label, vtiger_relatedlists.name, vtiger_tab.name as module_name FROM
                vtiger_relatedlists
                    INNER JOIN vtiger_tab ON(vtiger_tab.tabid = vtiger_relatedlists.related_tabid)
                WHERE vtiger_relatedlists.tabid = '".getTabId($module_name)."' AND related_tabid not in (SELECT tabid FROM vtiger_tab WHERE presence = 1) ORDER BY sequence, vtiger_relatedlists.relation_id";
        $result = $adb->query($sql);

        $relatedLists = array();
        while($row = $adb->fetch_array($result)) {

            // Nur wenn Zugriff erlaubt, dann zugreifen lassen
            if ($profileTabsPermission[$row["related_tabid"]] == 0) {
                if ($profileActionPermission[$row["related_tabid"]][3] == 0) {
                    $relatedLists[] = array(
                        "related_tabid" => $row["related_tabid"],
                        "module_name" => $row["module_name"],
                        "action" => $row["name"],
                        "label" => isset($app_strings[$row["label"]])?$app_strings[$row["label"]]:$row["label"],
                    );
                }
            }

        }

        return $relatedLists;
    }

    public static function getModuleName($tabid) {
        global $adb;

        $sql = "SELECT name FROM vtiger_tab WHERE tabid = ".intval($tabid);
        $result = $adb->query($sql);

        return $adb->query_result($result, 0, "name");
    }

    public static function formatUserDate($date) {
        if(class_exists("DateTimeField")) {
            return DateTimeField::convertToUserFormat($date);
        } else {
            return $date;
        }
    }

    public static function convertToUserTZ($date) {
        if(class_exists("DateTimeField")) {
            $return = DateTimeField::convertToUserTimeZone($date);
            return $return->format("Y-m-d H:i:s");
        } else {
            return $date;
        }
    }

    public static function describeModule($moduleName, $loadReferences = false, $nameFormat = "###") {
        global $current_user;
        $columnsRewrites = array(
            "assigned_user_id" => "smownerid"
        );
        $loadedRefModules = array();

        require_once("include/Webservices/DescribeObject.php");
        $refFields = array();
        $return = array();
        $describe = vtws_describe($moduleName, $current_user);

        $return["crmid"] = array(
            "name" => "crmid",
            "label" => "ID",
            "mandatory" => false,
            "type" => array("name" => "string"),
            "editable" => false
        );

        foreach($describe["fields"] as $field) {
            if(!empty($columnsRewrites[$field["name"]])) {
                $field["name"] = $columnsRewrites[$field["name"]];
            }
            if($field["name"] == "smownerid") {
                $field["type"]["name"] = "reference";
                $field["type"]["refersTo"] = array("Users");
            }

            if($field["type"]["name"] == "reference" && $loadReferences == true) {
                foreach($field["type"]["refersTo"] as $refModule) {
                    #if(!empty($loadedRefModules[$refModule])) continue;

                    $refFields = array_merge($refFields, self::describeModule($refModule, false, "(".$field["name"].": (".$refModule.") ###)"));

                    #var_dump($refFields);
                    $loadedRefModules[$refModule] = "1";
                }
            }

            $fieldName = str_replace("###", $field["name"], $nameFormat);

            $return[$fieldName] = $field;

        }

        /** Assigned Users */
        global $adb;
        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);
        while($user = $adb->fetchByAssoc($result)) {
            $user["id"] = "19x".$user["id"];
            $availUser["user"][] = $user;
        }
        $sql = "SELECT * FROM vtiger_groups ORDER BY groupname";
        $result = $adb->query($sql);
        while($group = $adb->fetchByAssoc($result)) {
            $group["groupid"] = "20x".$group["groupid"];
            $availUser["group"][] = $group;
        }
        /** Assigned Users End */

        $return["assigned_user_id"]["type"]["name"] = "picklist";
        $return["assigned_user_id"]["type"]["picklistValues"] = array();

        $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => '$currentUser', "value" => '$current_user_id');

        for($a = 0; $a < count($availUser["user"]); $a++) {
            $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => $availUser["user"][$a]["user_name"], "value" => $availUser["user"][$a]["id"]);
        }
        for($a = 0; $a < count($availUser["group"]); $a++) {
            $return["assigned_user_id"]["type"]["picklistValues"][] = array("label" => "Group: " . $availUser["group"][$a]["groupname"], "value" => $availUser["group"][$a]["groupid"]);
        }

        $return["smownerid"] = $return["assigned_user_id"];


        $return = array_merge($return, $refFields);

        return $return;
    }

    public static function existTable($tableName) {
        global $adb;
        $tables = $adb->get_tables();

        foreach($tables as $table) {
            if($table == $tableName)
                return true;
        }

        return false;
    }
    public static function checkColumn($table, $colum, $type) {
        global $adb;

        $result = $adb->query("SHOW COLUMNS FROM `".$table."` LIKE '".$colum."'");
        $exists = ($adb->num_rows($result))?true:false;

        if($exists == false) {
            $adb->query("ALTER TABLE `".$table."` ADD `".$colum."` ".$type." NOT NULL");
        }

        return $exists;

    }

    public static function is_utf8($str){
      $strlen = strlen($str);
      for($i=0; $i<$strlen; $i++){
        $ord = ord($str[$i]);
        if($ord < 0x80) continue; // 0bbbbbbb
        elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
        elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
        elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
        else return false; // ungültiges UTF-8-Zeichen
        for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
          if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80)
            return false; // ungültiges UTF-8-Zeichen
      }
      return true; // kein ungültiges UTF-8-Zeichen gefunden
    }

    public static function decodeExpressions($expression) {
        $expression = preg_replace_callback('/\\$\{(.*)\}\}&gt;/s', array("VtUtils", "_decodeExpressions"), $expression);

        return $expression;
    }
    public static function maskExpressions($expression) {
        $expression = preg_replace_callback('/\\$\{(.*)\}\}>/s', array("VtUtils", "_maskExpressions"), $expression);

        return $expression;
    }
    protected static function _maskExpressions($match) {
        return '${ ' . htmlentities(($match[1])) . ' }}>';
    }
    protected static function _decodeExpressions($match) {
        return '${ ' . html_entity_decode(htmlspecialchars_decode($match[1])) . ' }}>';
    }


}
}