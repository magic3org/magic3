<?php

Designer::load('Designer_Data_Mapper');

class Designer_Data_ExtensionMapper extends Designer_Data_Mapper
{
    function __construct()
    {
        parent::__construct('Extension', 'extensions', 'extension_id');
    }

    function find($filter = array())
    {
        $where = array();
        if (isset($filter['element']))
            $where[] = 'element = ' . $this->_db->Quote($this->_db->escape($filter['element'], true), false);
        $result = $this->_loadObjects($where, isset($filter['limit']) ? (int)$filter['limit'] : 0);
        return $result;
    }

    function create()
    {
        $row = $this->_create();
        return $row;
    }
}