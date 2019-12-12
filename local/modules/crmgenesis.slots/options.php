<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Iblock\ElementTable,
    Bitrix\Main\GroupTable,
    \Crmgenesis\Slots\Bitrixfunction;

$moduleId = basename( __DIR__ );
$moduleLangPrefix = strtoupper( str_replace( ".", "_", $moduleId ) );
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
Loc ::loadMessages( __FILE__ );

if ( $APPLICATION -> GetGroupRight( $moduleId ) < "R" )
{
    $APPLICATION -> AuthForm( Loc ::getMessage( "ACCESS_DENIED" ) );
}

Loader ::includeModule( $moduleId );


//массив всех списков для селектов
$resIblock = [];
//$resIblock[] = Loc::getMessage('CRM_GENESIS_NOT_SELECTED_FIELD_LABEL');
//$rsIblock = \Bitrix\Iblock\IblockTable::getList(array(
//    'select' =>  array('ID', 'NAME'),
//));
//while ($arIblock = $rsIblock->fetch())
//{
//    $resIblock[$arIblock['ID']] = $arIblock['NAME'].' ('.$arIblock['ID'].')';
//}
$resIblock = Bitrixfunction::getListsArrForOptionsPhp();
//массив всех списков для селектов

//массив для выбора DEFAULT STATUS 09.12.2019
$defStatusValList = [];
//$statusListID = Bitrixfunction::getCoptionValue('SLOT_STATUS_LIST');
//if($statusListID > 0){
//    $defStList = \Bitrix\Iblock\ElementTable::getList([
//        'select' => ['ID','NAME'],
//        'filter' => ['IBLOCK_ID' => $statusListID],
//        'order' => ['DATE_CREATE' => 'DESC'],
//    ]);
//    while($ob = $defStList->fetch())
//        $defStatusValList[$ob['ID']] = $ob['NAME'].' ('.$ob['ID'].')';
//}
$defStatusValList = Bitrixfunction::getDefaultStatusFromListForOptionPhp();

//массив пользовательских групп
$userGroupsArr = Bitrixfunction::getGroupsArrFroOprionPhp();


$aTabs = [
    [
        'DIV' => 'crmgenesis',
        'TAB' => Loc::getMessage('CRM_GENESIS_SLOTS_TAB1_INFO'),
        'TITLE' => Loc::getMessage("CRM_GENESIS_SLOTS_TAB1_INFO_DESCRIPRION"),
        'OPTIONS' => [

            Loc::getMessage('CRM_GENESIS_SLOTS_TAB1_INNER_TITLE'),
            [
                'SLOT_TYPE_LIST',
                Loc::getMessage( 'CRM_GENESIS_SLOTS_TYPE_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_STATUS_LIST',// создаст COption('SLOT_STATUS_LIST'), потом можно его брать
                Loc::getMessage( 'CRM_GENESIS_SLOTS_STATUS_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_SERVISE_LIST', // создаст COption('SLOT_SERVISE_LIST'), потом можно его брать
                Loc::getMessage( 'CRM_GENESIS_SLOTS_SERVISE_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_CLUB_LIST',
                Loc::getMessage( 'CRM_GENESIS_SLOTS_CLUBS_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_ZONA_LIST',
                Loc::getMessage( 'CRM_GENESIS_SLOTS_ZONAS_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_LOCATION_LIST',
                Loc::getMessage( 'CRM_GENESIS_SLOTS_LOCATIONS_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],
            [
                'SLOT_TRAINING_GROUP_LIST',
                Loc::getMessage( 'CRM_GENESIS_SLOTS_TRAINING_GROUP_LIST_FIELD_LABEL' ),
                '',
                ['selectbox', $resIblock]
            ],

        ]
    ],
    [
        'DIV' => 'crmgenesis1',
        'TAB' => Loc::getMessage('CRM_GENESIS_SLOTS_TAB2_INFO'),
        'TITLE' => Loc::getMessage("CRM_GENESIS_SLOTS_TAB2_INFO_DESCRIPRION"),
        'OPTIONS' => [
            Loc::getMessage('CRM_GENESIS_SLOTS_TAB3_INNER_TITLE'),
            [
                'SLOT_DEFAULT_STATUS',// создаст COption('SLOT_DEFAULT_STATUS'), потом можно его брать
                Loc::getMessage( 'CRM_GENESIS_SLOTS_STATUS_DEFAULT_VALUE_LABEL' ),
                '',
                ['selectbox', $defStatusValList]
            ],
        ],
    ],
    [
        'DIV' => 'crmgenesis2',
        'TAB' => Loc::getMessage('CRM_GENESIS_SLOTS_TAB3_INFO'),
        'TITLE' => Loc::getMessage("CRM_GENESIS_SLOTS_TAB3_INFO_DESCRIPRION"),
        'OPTIONS' => [
            Loc::getMessage('CRM_GENESIS_SLOTS_TAB3_INNER_TITLE'),
            [
                'HOLE_ADMINISTRATOR_GROUP',
                Loc::getMessage( 'CRM_GENESIS_HOLE_ADMINISTRATOR_GROUP_FIELD_LABEL' ),
                '',
                ['selectbox', $userGroupsArr]
            ],
        ],
    ],
];


if ( $request -> isPost() && check_bitrix_sessid() )
{
    if ( strlen( $request[ 'save' ] ) > 0 )
    {
        foreach ( $aTabs as $arTab )
        {
            if($arTab["TYPE"] != 'rights')
                __AdmSettingsSaveOptions( $moduleId, $arTab['OPTIONS']);
        }
    }
}
$tabControl = new CAdminTabControl( 'tabControl', $aTabs );
$realModuleId = $moduleId;
?>
<form method='post' action='<? echo $APPLICATION -> GetCurPage() ?>?mid=<?= $moduleId ?>&amp;lang=<?= $request[ 'lang' ] ?>'
      name='<?= $moduleId ?>_settings'>
    <? $tabControl -> Begin(); ?>
    <?
    foreach ( $aTabs as $aTab ):
        $tabControl -> BeginNextTab();
        ?>
        <?
        if ( $aTab[ 'OPTIONS' ] ):
            __AdmSettingsDrawList( $moduleId, $aTab[ 'OPTIONS' ] );
        elseif( $aTab["TYPE"] == 'rights' ):
            $table_id = $moduleId ."_". strtolower( $aTab["POSTFIX"] );
            require( __DIR__ . "/table_rights.php" );
            $moduleId = $realModuleId;
        endif;?>

    <?endforeach;
    ?>
    <?= bitrix_sessid_post();
    $tabControl -> Buttons( array( 'btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false, "btnSave" => true ) );
    ?>
    <? $tabControl -> End(); ?>

    <?//need for tab_rights. If in $_REQUEST hasn't Update -> rights do not save?>
    <input type="hidden" name="Update" value="Y" />

</form>
