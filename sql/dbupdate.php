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
<#2>
<?php
// Old version
?>
<#3>
<?php
// Old version
?>
<#4>
<?php
// Old version
?>
<#5>
<?php
// Old version
?>
<#6>
<?php
global $DIC;
$db = $DIC->database();
if ($db->tableExists('page_object')) {
    $query = "SELECT page_id, parent_id, content, rendered_content, lang 
              FROM page_object 
              WHERE content LIKE " . $db->quote('%SrGeogebra%', 'text') . " 
              OR rendered_content LIKE " . $db->quote('%SrGeogebra%', 'text');

    $res = $db->query($query);

    while ($row = $db->fetchAssoc($res)) {
        $updatedContent = str_replace('SrGeogebra', 'GeoGebra', $row['content']);
        $updatedRenderedContent = str_replace('SrGeogebra', 'GeoGebra', $row['rendered_content']);

        // Solo actualizar si hay cambios en el contenido o rendered_content
        if ($updatedContent !== $row['content'] || $updatedRenderedContent !== $row['rendered_content']) {
            $updateQuery = "UPDATE page_object 
                            SET content = " . $db->quote($updatedContent, 'text') . ",
                                rendered_content = " . $db->quote($updatedRenderedContent, 'text') . "
                            WHERE page_id = " . $db->quote($row['page_id'], 'integer') . " 
                            AND parent_id = " . $db->quote($row['parent_id'], 'integer') . "
                            AND lang = " . $db->quote($row['lang'], 'text');
            $db->manipulate($updateQuery);
        }
    }
}
if ($db->tableExists('page_history')) {
    $query = "SELECT page_id, nr, user_id, content 
          FROM page_history 
          WHERE content LIKE " . $db->quote('%SrGeogebra%', 'text');
    $res = $db->query($query);

    while ($row = $db->fetchAssoc($res)) {
        $updatedContent = str_replace('SrGeogebra', 'GeoGebra', $row['content']);

        if ($updatedContent !== $row['content']) {
            $updateQuery = "UPDATE page_history 
                        SET content = " . $db->quote($updatedContent, 'text') . " 
                        WHERE page_id = " . $db->quote($row['page_id'], 'integer') . " 
                        AND nr = " . $db->quote($row['nr'], 'integer') . " 
                        AND user_id = " . $db->quote($row['user_id'], 'integer');
            $db->manipulate($updateQuery);
        }
    }
}
?>