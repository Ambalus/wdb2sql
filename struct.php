<?php

if(!defined('INDEX_PRIMORY_KEY')){
	define('INDEX_PRIMORY_KEY',1);
}
define('STRUCT_DATA','struct');
// wdb format v3.3.5/3.3.5a
$WDBstruct = array(
'creaturecache' => array(
	0 => array('Id',INDEX_PRIMORY_KEY),
    // <wdbElement type="size" />
	1 => 'Name',
	2 => 'Subname',
	3 => 'IconName',
	4 => 'Flags',
	5 => 'Type',
	6 => 'Family',
	7 => 'Rank',
	8 => array('KillCredit',2),
	10=> array('ModelId',4),
	14=> 'HealthModifier',
	15=> 'PowerModifier',
	16=> 'RacialLeader',
	17=> array('QuestItem',6),
	23=> 'MovementId'	
),
'gameobjectcache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
    1 => 'Type', 						// uinteger
    2 => 'DisplayId', 					// uinteger
    3 => 'Name', 						// varChar
    // <wdbElement type="varChar" />
    // <wdbElement type="varChar" />
    // <wdbElement type="varChar" />
    4 => 'IconName', 					// varChar
    5 => 'CastBarCaption', 				// varChar
    6 => 'Unk1', 						// varChar
    7 => array('data',24), 				// uinteger
    31=> 'Size', 						// single
    32=> array('QuestItem',6) 			// uinteger
),
'itemnamecache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
    1 => 'Name',						// varChar
    2 => 'InventoryType'				// uinteger
),
'itemtextcache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
    1 => 'Text', 						// varChar
),
'npccache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
	1 => array(
			array(
				'prob',					// single 
				array('text%d_',2), 		// varChar
				'lang',					// uinteger
				array('em%d_',6),			// uinteger
			),
			STRUCT_DATA,8
		)
),
'pagetextcache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
    1 => 'Text', 						// varChar
    2 => 'NextPage' 					// uinteger
),
'questcache' => array(
    0 => array('Id',INDEX_PRIMORY_KEY),	// uinteger
    // <wdbElement type="size" />
    // <wdbElement type="uinteger" />
    1 => 'Method',						// uinteger
    2 => 'QuestLevel', 					// integer
    3 => 'MinLevel',					// uinteger
    4 => 'ZoneOrSort', 					// integer
    5 => 'Type',						// uinteger
    6 => 'SuggestedPlayers',			// uinteger
    7 => array(
			array(
				'RepObjectiveFaction',	// uinteger 
				'RepObjectiveValue',	// uinteger
			),
			STRUCT_DATA,2
		),
    11=> 'NextQuestInChain',			// uinteger
    12=> 'Unk1', 						// integer
    13=> 'RewOrReqMoney', 				// integer
    14=> 'RewMoneyMaxLevel',			// uinteger 
    15=> 'RewSpell',					// uinteger
    16=> 'RewSpellCast',				// uinteger
    17=> 'Honor1',						// uinteger
    18=> 'Honor2', 						// single
    19=> 'SrcItemId',					// uinteger
    20=> 'QuestFlags',					// uinteger
    21=> 'CharTitleId',					// uinteger
    22=> 'PlayersSlain', 				// integer
    23=> 'BonusTalents', 				// integer
    24=> 'BonusArenaPoints', 			// integer
    25=> 'Unk2', 						// integer
	26=> array(
			array(
				'RewItemId',			// uinteger 
				'RewItemCount',			// uinteger
			),
			STRUCT_DATA,4
		),
	34=> array(
			array(
				'RewChoiceItemId',		// uinteger
				'RewChoiceItemCount',	// uinteger
			),
			STRUCT_DATA,6
		),
    46=> array('RawFactionId',5),		// uinteger
    51=> array('RawFactionVal',5), 		// integer
    56=> array('RawFactionValOverride',5),// uinteger
    61=> 'PointMapId',					// uinteger
    62=> 'PointX', 						// single
    63=> 'PointY', 						// single
    64=> 'Unk3', 						// integer
    65=> 'Title', 						// varChar
    66=> 'Objectives',					// varChar
    67=> 'Details', 					// varChar
    68=> 'ToDoText', 					// varChar
    69=> 'EndText', 					// varChar
    70=> array(
			array(
				'ReqCreatureOrGOId', 	// integer
				'ReqCreatureOrGOCount',	// uinteger
				'ReqSourceId',			// uinteger
				'ReqSourceIdMaxCount',	// uinteger
			),
			STRUCT_DATA,4
		),
    86=> array(
			array(
				'ReqItemId',			// uinteger
				'ReqItemCount',			// uinteger
			),
			STRUCT_DATA,6
		),
    98=> array('ObjectiveText',4) 		// varChar
)
);

$ADBstruct = array(
'Item' => array(),
'ItemCurrencyCost' => array(),
'ItemExtendedCost' => array(),
'Item-sparse' => array()
);