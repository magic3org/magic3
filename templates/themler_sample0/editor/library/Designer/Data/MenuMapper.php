<?php

Designer::load('Designer_Data_Mapper');

class Designer_Data_MenuMapper extends Designer_Data_Mapper
{
    function __construct()
    {
        parent::__construct('MenuType', 'menu_types', 'id');
    }

    function find($filter = array())
    {
        $where = array();
        if (isset($filter['title']))
            $where[] = 'title = ' . $this->_db->Quote($this->_db->escape($filter['title'], true), false);
        $result = $this->_loadObjects($where, isset($filter['limit']) ? (int)$filter['limit'] : 0);
        return $result;
    }

    function create()
    {
        $row = $this->_create();
        return $row;
    }

    function delete($id)
    {
        // Delete related records in the modules_menu table.

        // Start with checking whether this menu exists: 
        $menu = $this->fetch($id);
        if (is_string($menu))
            return $this->_error($menu, 1);

        // Get the menu:
        $this->_db->setQuery('SELECT menutype FROM #__menu_types WHERE id=' . $this->_db->Quote($id));
        $menutype = $this->_db->loadResult();
        if ($this->_db->getErrorNum())
            return $this->_error($this->_db->stderr(), 1);

        if (is_string($menutype)) {
            // Select items for the specified menu:
            $this->_db->setQuery('SELECT id FROM #__menu WHERE menutype=' . $this->_db->Quote($menutype) . ' ORDER BY id');
            $items = $this->_db->loadColumn(0);
            if ($this->_db->getErrorNum())
                return $this->_error($this->_db->stderr(), 1);

            $items = array_map('intval', $items);

            if (0 < count($items)) {
                // Delete "Only on the pages selected" assignments:
                $this->_db->setQuery('DELETE FROM #__modules_menu WHERE menuid in (' . implode(',', $items) . ')');
                $this->_db->query();
                if ($this->_db->getErrorNum())
                    return $this->_error($this->_db->stderr(), 1);

                // Invert items:
                for ($i = 0, $limit = count($items); $i < $limit; $i++)
                    $items[$i] = -$items[$i];

                // Get the modules that are not shown on the menu items that are about to be deleted:
                $this->_db->setQuery('SELECT moduleid FROM #__modules_menu WHERE menuid in (' . implode(',', $items) . ')');
                $modules = $this->_db->loadColumn(0);
                if ($this->_db->getErrorNum())
                    return $this->_error($this->_db->stderr(), 1);

                $modules = array_unique($modules);

                // delete "On all pages except those selected" assignment:
                $this->_db->setQuery('DELETE FROM #__modules_menu WHERE menuid in (' . implode(',', $items) . ')');
                $this->_db->query();
                if ($this->_db->getErrorNum())
                    return $this->_error($this->_db->stderr(), 1);

                // restore modules "On all pages" state:
                foreach ($modules as $module) {
                    $this->_db->setQuery('SELECT COUNT(*) FROM #__modules_menu WHERE moduleid=' . $this->_db->Quote($module));
                    $count = (int)$this->_db->loadResult();
                    if ($this->_db->getErrorNum())
                        return $this->_error($this->_db->stderr(), 1);

                    if (0 == $count) {
                        $this->_db->setQuery('INSERT INTO #__modules_menu (moduleid, menuid) VALUES (' . $this->_db->Quote($module) . ', 0)');
                        $this->_db->query();
                        if ($this->_db->getErrorNum())
                            return $this->_error($this->_db->stderr(), 1);
                    }
                }
            }
        }
        return parent::delete($id);
    }
}