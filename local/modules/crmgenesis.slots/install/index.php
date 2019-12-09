<?

//подключение файла с какими-то данными модуля и проверкой на D7
include_once(dirname(__DIR__).'/lib/main.php');

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;

//Это подключение файла с классом тек. модуля
use \Crmgenesis\Slots\Main;


class crmgenesis_slots extends \CModule{

    public $MODULE_ID = 'crmgenesis.slots';

    public function __construct(){
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("CRM_GENESIS_SLOTS_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("CRM_GENESIS_SLOTS_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("CRM_GENESIS_SLOTS_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("CRM_GENESIS_SLOTS_PARTNER_URI");
    }

    public function InstallEvents(){}
    public function UnInstallEvents(){}

    public function InstallFiles(){
        CopyDirFiles(Main::GetPatch()."/install/slot_workdesk/", $_SERVER["DOCUMENT_ROOT"]."/slot_workdesk/", true, true);
        CopyDirFiles(Main::GetPatch()."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/local/components/", true, true);
    }

    public function UnInstallFiles(){
        DeleteDirFilesEx("/slot_workdesk/");
        DeleteDirFilesEx("/local/components/crmgenesis/slots.component");

        //удаление папки crmgenesis из компонентов, если в ней пусто после удаления своего компонента
        if(!glob($_SERVER['DOCUMENT_ROOT'].'/local/components/crmgenesis/*'))
            DeleteDirFilesEx("/local/components/crmgenesis");
    }

    public function InstallDB()
    {
        global $DB;
        $res = $DB->RunSQLBatch(__DIR__ . "/db/" . strtolower($DB->type) . '/install.sql');

        self::logData($res);

        return true;
    }

    public function UnInstallDB($arParams = array())
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__ . "/db/" . strtolower($DB->type) . '/uninstall.sql');
        return true;
    }

    public function DoInstall(){
        global $APPLICATION;
        if(Main::isVersionD7())
        {
            $this->InstallFiles();
            $this->InstallEvents();
            $this->InstallDB();
            ModuleManager::registerModule($this->MODULE_ID);
        }
        else
            $APPLICATION->ThrowException(Loc::getMessage("CRM_GENESIS_SLOTS_ERROR_VERSION"));
    }

    public function DoUninstall(){
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();

        //удаление сохраненных в COption
        //\Bitrix\Main\Config\Option::delete($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function logData($data){
        $file = $_SERVER["DOCUMENT_ROOT"].'/zzz.log';
        file_put_contents($file, print_r([date('d.m.Y H:i:s'),$data],true), FILE_APPEND | LOCK_EX);
    }

}