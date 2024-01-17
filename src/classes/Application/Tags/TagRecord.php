<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application_Admin_ScreenInterface;
use Application\Area\Tags\BaseViewTagScreen;
use DBHelper_BaseRecord;

/**
 * @package Application
 * @subpackage Tags
 *
 * @property TagCollection $collection
 * @method TagCollection getCollection()
 */
class TagRecord extends DBHelper_BaseRecord
{
    protected function init() : void
    {
    }

    public function getSubTags() : array
    {
        return $this->getSubTagCriteria()->getItemsObjects();
    }

    public function getSubTagCriteria() : TagCriteria
    {
        return AppFactory::createTags()
            ->getFilterCriteria()
            ->selectParentTag($this);
    }

    public function addSubTag(string $label) : TagRecord
    {
        return AppFactory::createTags()->createNewTag($label, $this);
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(TagCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()
            ->link($this->getLabel(), $this->getAdminURL());
    }

    public function getParentTagID() : ?int
    {
        $id = $this->getRecordIntKey(TagCollection::COL_PARENT_TAG_ID);
        if($id > 0) {
            return $id;
        }

        return null;
    }

    public function getParentTag() : ?TagRecord
    {
        $parentTagID = $this->getParentTagID();

        if($parentTagID !== null) {
            return AppFactory::createTags()->getByID($parentTagID);
        }

        return null;
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseViewTagScreen::URL_NAME;
        $params[TagCollection::PRIMARY_NAME] = $this->getID();

        return $this->collection->getAdminURL($params);
    }
}
