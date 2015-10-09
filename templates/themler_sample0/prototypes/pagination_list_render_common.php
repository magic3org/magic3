<?php

function pagination_list_render_common($list)
{
    // Initialise variables.
    $lang = JFactory::getLanguage();
?>
    <ul class=" bd-pagination pagination">
    <li class=" bd-paginationitem-1 disabled"><a href="#">Prev</a></li>
<li class=" bd-paginationitem-1 active"><a href="#">1</a></li>
<li class=" bd-paginationitem-1"><a href="#">2</a></li>
<li class=" bd-paginationitem-1"><a href="#">3</a></li>
<li class=" bd-paginationitem-1"><a href="#">4</a></li>
<li class=" bd-paginationitem-1"><a href="#">5</a></li>
<li class=" bd-paginationitem-1"><a href="#">Next</a></li>
</ul>
<?php
}