<?php

class Rejoiner_Acr_Model_System_Config_Source_Salesrule  {

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options   = array();
        $additional= array(
            'value' => 'rule_id',
            'label' => 'name'
        );
        $collection = Mage::getResourceModel('salesrule/rule_collection')->loadData();
        foreach ($collection as $item) {
            if ($item->getUseAutoGeneration()) {
                $data = array();
                foreach ($additional as $code => $field) {
                    $data[$code] = $item->getData($field);
                }
                $options[] = $data;
            }
        }
        array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        return $options;
    }

} 