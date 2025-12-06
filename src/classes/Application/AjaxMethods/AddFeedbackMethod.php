<?php

declare(strict_types=1);

namespace Application\AjaxMethods;

use Application;
use Application_AjaxMethod;
use Application\Feedback\FeedbackCollection;

class AddFeedbackMethod extends Application_AjaxMethod
{
    public const METHOD_NAME = 'AddFeedback';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $report = $this->collection->addFeedback(
            $this->type,
            $this->scope,
            $this->text,
            $this->url
        );

        $this->sendResponse(array(
            FeedbackCollection::PRIMARY_NAME => $report->getID()
        ));
    }

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @var FeedbackCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $text;

    protected function validateRequest() : void
    {
        $this->collection = Application::createFeedback();

        $this->scope = (string)$this->request->registerParam('scope')
            ->setEnum($this->collection->getScopeIDs())
            ->get();

        if (empty($this->scope)) {
            $this->sendErrorUnknownElement('Scope');
        }

        $this->type = (string)$this->request->registerParam('type')
            ->setEnum($this->collection->getTypeIDs())
            ->get();

        if (empty($this->type)) {
            $this->sendErrorUnknownElement('Type');
        }

        $this->url = (string)$this->request->registerParam('url')
            ->setURL()
            ->get();

        if (empty($this->url)) {
            $this->sendErrorUnknownElement('URL');
        }

        $this->text = (string)$this->request->registerParam('feedback')
            ->addFilterTrim()
            ->addHTMLSpecialcharsFilter()
            ->get();

        if (empty($this->text)) {
            $this->sendErrorUnknownElement('Feedback text');
        }
    }
}
