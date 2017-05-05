<?php

class ob_res_ipfield implements ob_res_inter {
    const FIELD_TEXT = 'FIELD_TEXT';

    private $fields = array();

    public function addField($fieldID, $fieldName, $fieldType = self::FIELD_TEXT) {
        $this->fields[] = array($fieldID, $fieldName, $fieldType);
    }

    public function getRes() {
        return $this->fields;
    }
}

?>
