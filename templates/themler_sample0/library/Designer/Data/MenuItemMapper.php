<?php

Designer::load('Designer_Data_Mapper');

class Designer_Data_MenuItemMapper extends Designer_Data_Mapper
{
    function __construct()
    {
        parent::__construct('Menu', 'menu', 'id');
    }

    function find($filter = array())
    {
        $where = array();
        if (isset($filter['menu']))
            $where[] = 'menutype = ' . $this->_db->Quote($filter['menu']);
        if (isset($filter['title']))
            $where[] = 'title = ' . $this->_db->Quote($filter['title']);
        if (isset($filter['home']))
            $where[] = 'home = ' . $this->_db->Quote($filter['home']);
        if (isset($filter['scope']) && ('site' == $filter['scope'] || 'administrator' == $filter['scope']))
            $where[] = 'client_id = ' . ('site' == $filter['scope'] ? '0' : '1');
        $result = $this->_loadObjects($where, isset($filter['limit']) ? (int)$filter['limit'] : 0);
        return $result;
    }

    function create()
    {
        $row = $this->_create();
        $row->published = '1';
        $row->access = 1;
        $row->language = '*';
        $row->setLocation(1, 'last-child'); 
        return $row;
    }
}