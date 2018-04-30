<?php

$installer = $this;
$installer->startSetup();

$installer->run("
        ALTER TABLE `{$installer->getTable('sales_flat_quote')}`
            ADD `promo` VARCHAR(255) NULL;
    ");

$installer->endSetup();