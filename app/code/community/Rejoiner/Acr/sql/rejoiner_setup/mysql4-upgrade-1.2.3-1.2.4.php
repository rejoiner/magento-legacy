<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$connection->beginTransaction();
try {
    if (!$connection->tableColumnExists($installer->getTable('newsletter/subscriber'), 'added_to_rejoiner')) {
        $connection->addColumn(
            $installer->getTable('newsletter/subscriber'), 'added_to_rejoiner', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => false,
            'default' => 0,
            'comment' => 'Added To Rejoiner'
        ));
    }

    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_checkout_onepage_index', 1);
    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_customer_account_create', 1);
    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_customer_account_login', 1);
    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_newsletter_manage_index', 1);
    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_checkbox_default', 0);
    Mage::getConfig()->saveConfig('checkout/rejoiner_acr/subscribe_checkbox_label', 'Sign Up for Newsletter');

    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    throw $e;
}

$installer->endSetup();
