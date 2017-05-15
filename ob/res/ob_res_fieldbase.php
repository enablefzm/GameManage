<?php

class ob_res_fieldbase {
    const FIELD_TEXT = 'FIELD_TEXT';
    const FIELD_CHECKBOX = 'FIELD_CHECKBOX';
    const FIELD_TEXTAREA = 'FIELD_TEXTAREA';

    protected $fields = array();

    public function addField($fieldID, $fieldName, $fieldType = self::FIELD_TEXT) {
        $this->fields[] = array($fieldID, $fieldName, $fieldType);
    }

    public function getRes() {
        return $this->fields;
    }
}
?>
