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
$below = array();
$dropdown = null;

$nav->addClass('nav-pills');
$nav->addClass('subnav');

$menu = $nav->getContextMenu();

if($menu) 
{
	$dropdown = $this->ui->createButtonDropdown(mb_strtoupper(t('Menu')))
	->setMenu($menu)
	->makeNavItem()
	->noCaret()
	->setIcon(UI::icon()->menu())
	->addClass('pull-right');
}

?>

<!-- start subnav -->
<ul class="<?php echo implode(' ', $nav->getClasses()) ?>">

<?php 
    if($dropdown) 
    {
        echo '<!-- start button dropdown -->'.$dropdown->render().'<!-- end button dropdown -->';
    }

    foreach ($items as $item)
    {
        if($item->isPositionBelow())
        {
            $below[] = $item;
            continue;
        }

        $group = $item->getGroup();
        
        if ($group && in_array($group, $trackGroups)) 
        {
            continue;
        }
        
        if ($group) 
        {
            $jsID = 'nav' . nextJSID();
            $active = '';
            $liActive = '';
            $groupItems = $nav->getItemsByGroup($group);
            $groupLabel = $group;
            
            
            if ($nav->isGroupActive($group)) {
                $liActive = ' active';
                $active = ' nav-group-active';
                
                // append the name of the active subitem to the
                // group label
                foreach ($groupItems as $groupItem) {
                    if ($groupItem->isActive()) {
                        $groupLabel .= ': ' . $groupItem->getTitle();
                        break;
                    }
                }
            }
            
            ?>
            	<li class="dropdown <?php echo $liActive ?>">
            		<a id="<?php echo $jsID ?>" class="nav-group dropdown-toggle <?php echo $active ?>" data-toggle="dropdown" href="#">
            			<?php echo $groupLabel ?>
            			<?php UI::icon()->dropdown()->display() ?> 
        			</a>
            		<ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo $jsID ?>">
            			<?php 
            			    foreach ($groupItems as $groupItem) 
            			    {
            			        $groupActive = '';
            			        
                                if ($groupItem->isActive()) 
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
            $classes = $item->getContainerClasses();
            
            if ($item->isActive()) {
                $classes[] = 'active';
            } else {
                $classes[] = 'regular';
            }
            
            ?>
            	<li class="<?php echo implode(' ', $classes) ?>"> 
                	<?php echo $item->render() ?> 
            	</li>
        	<?php
        }
    }
?>
</ul>
<?php

    foreach ($below as $item)
    {
        echo $item->render();
    }

?>
<!-- end subnav -->
