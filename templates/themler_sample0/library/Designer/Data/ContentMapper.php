<?php

Designer::load('Designer_Data_Mapper');

class Designer_Data_ContentMapper extends Designer_Data_Mapper
{
    function __construct()
    {
        parent::__construct('content', 'content', 'id');
    }

    function find($filter = array())
    {
        $where = array();
        if (isset($filter['section']))
            $where[] = 'sectionid = ' . intval($filter['section']);
        if (isset($filter['category']))
            $where[] = 'catid = ' . intval($filter['category']);
        if (isset($filter['title']))
            $where[] = 'title = ' . $this->_db->Quote($this->_db->escape($filter['title'], true), false);
        $result = $this->_loadObjects($where, isset($filter['limit']) ? (int)$filter['limit'] : 0);
        return $result;
    }

    function create()
    {
        $row = $this->_create();
        $row->state = '1';
        $row->version = '1';
        $row->language = '*';
        $row->created = JFactory::getDate()->toSql();
        $row->publish_up = $row->created;
        $row->publish_down = $this->_db->getNullDate();
        return $row;
    }

    function save($row)
    {
        JPluginHelper::importPlugin('content');

        $isNew = (bool)$row->id;
        if (!$row->check())
            return $this->_error($row->getError(), 1);
        $dispatcher = JDispatcher::getInstance();
        $result = $dispatcher->trigger('onBeforeContentSave', array($row, $isNew));
        if(in_array(false, $result, true))
            return $this->_error($row->getError(), 1);
        if (!$row->store())
            return $this->_error($row->getError(), 1);
        $row->checkin();
        $row->reorder('catid = ' . (int)$row->catid . ' AND state >= 0');
        $cache = JFactory::getCache('com_content');
        $cache->clean();
        $dispatcher->trigger('onAfterContentSave', array($row, $isNew));
        return null;
    }
}