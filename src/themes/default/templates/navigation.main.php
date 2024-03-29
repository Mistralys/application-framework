<?php

/* @var $this UI_Page_Template */
/* @var $nav UI_Page_Navigation */
/* @var $item UI_Page_Navigation_Item */

if (!$this->user->canLogin()) {
    return;
}

$nav = $this->getVar('navigation');
$items = $nav->getItems();
$trackGroups = array();

$nav->addClass('navbar-nav');
$nav->addClass('navbar-main');

?>
<!-- start main navigation -->
<ul class="<?php echo implode(' ', $nav->getClasses()) ?>">
	<?php
        foreach ($items as $item) 
        {
            $group = $item->getGroup();
            
            if ($group && in_array($group, $trackGroups, true))
            {
                continue;
            }
        
            if ($group) 
            {
                $jsID = 'nav' . nextJSID();
                $active = '';
                $groupItems = $nav->getItemsByGroup($group);
                $groupLabel = $group;

                $activeItem = $nav->getActiveGroupItem($group);

                if ($activeItem !== null) {
                    $active = ' nav-group-active';
                    $groupLabel .= ' - '.$activeItem->getTitle();
                }
        
                ?>
                    <li class="dropdown">
                    	<a id="<?php echo $jsID ?>" class="nav-group dropdown-toggle <?php echo $active ?>" data-toggle="dropdown" href="#">
                    		<?php echo $groupLabel ?>
                    		<?php UI::icon()->dropdown()->display() ?>
                    	</a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo $jsID ?>">
                        	<?php 
                                foreach ($groupItems as $groupItem) 
                                {
                                    $groupActive = '';
                                    
                                    if($groupItem->isActive()) 
                                    {
                                        $groupActive = 'active';
                                    }
                                    
                                    ?>
										<li class="nav-menu-item <?php echo $groupActive ?>" role="menuitem">
											<?php echo $groupItem->render() ?>
										</li>
									<?php 
                                }
                            ?>
                        </ul>
                    </li>
        		<?php 
                    
                $trackGroups[] = $group;
                continue;
            }
        
            $item->addClass('nav-link');

            if($item instanceof UI_Page_Navigation_Item_DropdownMenu)
            {
                echo $item->render();
            }
            else
            {
                $type = 'regular';

                if ($item->isActive())
                {
                    $type = 'active';
                }

                ?>
                    <li class="<?php echo $type ?>">
                        <?php echo $item->render() ?>
                    </li>
                <?php
            }
        }
    ?>
</ul>
<!-- end main navigation -->