<#1>
<?php
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('srgg_config')) {
    $fields = [
        'name' => [
            'type' => 'text',
            'length' => 250,
            'notnull' => true
        ],
        'value' => [
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        ]
    ];

    $db->createTable('srgg_config', $fields);
    $db->addPrimaryKey('srgg_config', ['name']);
}
?>