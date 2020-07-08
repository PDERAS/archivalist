<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'table_name' => 'archives',
    'morph_name' => 'archivable',
    'archive_class' => \PDERAS\Archivalist\Models\Archive::class
];
