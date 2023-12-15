<?php
/**
 * @package UI
 * @see \UI\PaginationRenderer
 */

declare(strict_types=1);

namespace UI;

use Application_FilterCriteria;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\PaginationHelper;
use AppUtils\Traits\RenderableTrait;
use AppUtils\URLInfo;
use TestDriver\ClassFactory;
use UI;
use function AppUtils\parseURL;

/**
 * Helper class for rendering pagination controls.
 *
 * See {@see UI::createPagination()} to create an instance
 * of the renderer for a {@see PaginationHelper} instance.
 *
 * @package UI
 */
class PaginationRenderer implements RenderableInterface
{
    use RenderableTrait;

    private PaginationHelper $paginator;
    private array $items = array();
    private string $pageParam;
    private URLInfo $url;
    private bool $populated = false;
    private int $current;

    public function __construct(PaginationHelper $paginator, string $pageParamName, string $baseURL)
    {
        $this->paginator = $paginator;
        $this->pageParam = $pageParamName;
        $this->current = $this->paginator->getCurrentPage();
        $this->url = parseURL($baseURL);
    }

    public function configureFilters(Application_FilterCriteria $filters) : self
    {
        $filters->setLimit($this->paginator->getItemsPerPage(), $this->paginator->getOffsetStart());
        return $this;
    }

    public function setAdjacentPages(int $pages) : self
    {
        $this->paginator->setAdjacentPages($pages);
        return $this;
    }

    public function getOffset() : int
    {
        return $this->paginator->getOffsetStart();
    }

    public function getURL(int $page) : string
    {
        $url = clone $this->url;

        return $url
            ->setParam($this->pageParam, (string)$page)
            ->getNormalized();
    }

    private function populateItems() : void
    {
        if($this->populated) {
            return;
        }

        $this->populated = true;

        $this->addPrevious();
        $this->addFirst();

        $this->items[] = ' | ';

        $this->addPages();

        $this->items[] = ' | ';

        $this->addLast();
        $this->addNext();
    }

    private function addNext() : void
    {
        $btn = UI::button(t('Next').' '.UI::icon()->next())
            ->makeMini()
            ->link($this->getURL($this->paginator->getNextPage()));

        if($this->paginator->getNextPage() === $this->current) {
            $btn->disable();
        }

        $this->items[] = $btn;
    }

    private function addLast() : void
    {
        $btn = UI::button(t('Last').' '.UI::icon()->last())
            ->makeMini()
            ->setTooltip(t('The last page is %1$s.', '#'.$this->paginator->getLastPage()))
            ->link($this->getURL($this->paginator->getLastPage()));

        if($this->current === $this->paginator->getLastPage()) {
            $btn->disable();
        }

        $this->items[] = $btn;
    }

    private function addPages() : void
    {
        $numbers = $this->paginator->getPageNumbers();

        foreach($numbers as $number)
        {
            $btn = UI::button((string)$number)
                ->makeMini()
                ->link($this->getURL($number));

            if($number === $this->current) {
                $btn->makeInfo();
            }

            $this->items[] = $btn;
        }
    }

    private function addFirst() : void
    {
        $btn = UI::button(UI::icon()->first().' '.t('First'))
            ->makeMini()
            ->link($this->getURL(1));

        if($this->current === 1) {
            $btn->disable();
        }

        $this->items[] = $btn;
    }

    private function addPrevious() : void
    {
        $btn = UI::button(UI::icon()->previous().' '.t('Previous'))
            ->makeMini()
            ->link($this->getURL($this->paginator->getPreviousPage()));

        if(!$this->paginator->hasPreviousPage()) {
            $btn->disable();
        }

        $this->items[] = $btn;
    }

    public function render(): string
    {
        $this->populateItems();

        return implode(' ', $this->items);
    }

    public function getTotalPages() : int
    {
        return $this->paginator->getTotalPages();
    }

    public function setCurrentPage(int $pageNumber) : self
    {
        $this->paginator->setCurrentPage($pageNumber);
        $this->current = $pageNumber;

        return $this;
    }

    public function setCurrentPageFromRequest() : self
    {
        $activePage = ClassFactory::createRequest()->registerParam($this->pageParam)->setInteger()->getInt(1);
        $totalPages = $this->paginator->getTotalPages();

        if($activePage < 1) {
            return $this->setCurrentPage(1);
        }

        if($activePage > $totalPages) {
            return $this->setCurrentPage($totalPages);
        }

        return $this->setCurrentPage($activePage);
    }
}
