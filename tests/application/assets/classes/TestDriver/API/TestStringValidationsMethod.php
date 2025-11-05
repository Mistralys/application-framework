<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Parameters\CommonTypes\AliasParameter;
use Application\API\Parameters\CommonTypes\AlphabeticalParameter;
use Application\API\Parameters\CommonTypes\AlphanumericParameter;
use Application\API\Parameters\CommonTypes\DateParameter;
use Application\API\Parameters\CommonTypes\EmailParameter;
use Application\API\Parameters\CommonTypes\LabelParameter;
use Application\API\Parameters\CommonTypes\MD5Parameter;
use Application\API\Parameters\CommonTypes\NameOrTitleParameter;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;
use TestDriver\APIClasses\TestDriverAPIGroup;

class TestStringValidationsMethod extends BaseAPIMethod
    implements
        RequestRequestInterface,
        JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestStringValidations';

    public const string VERSION_1_0 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1_0;
    public const string PARAM_TITLE = 'title';
    public const string PARAM_ALPHA = 'alpha';
    public const string PARAM_MD5 = 'md5';
    public const string PARAM_LABEL = 'label';
    public const string PARAM_ALIAS = 'alias';
    public const string PARAM_DATE = 'date';
    public const string PARAM_EMAIL = 'email';
    public const string PARAM_ALNUM = 'alnum';

    private LabelParameter $labelParam;
    private AliasParameter $aliasParam;
    private DateParameter $dateParam;
    private EmailParameter $emailParam;
    private NameOrTitleParameter $titleParam;
    private AlphabeticalParameter $alphaParam;
    private MD5Parameter $md5Param;
    private AlphanumericParameter $alnumParam;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test method for string validations.';
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return array(
            self::VERSION_1_0
        );
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    protected function init(): void
    {
        $this->aliasParam = $this->addParam(self::PARAM_ALIAS, 'Alias')->alias(false);
        $this->alphaParam = $this->addParam(self::PARAM_ALPHA, 'Alphabetical')->alphabetical();
        $this->alnumParam = $this->addParam(self::PARAM_ALNUM, 'Alphanumeric')->alphanumeric();
        $this->dateParam = $this->addParam(self::PARAM_DATE, 'Date')->date();
        $this->emailParam = $this->addParam(self::PARAM_EMAIL, 'Email')->email();
        $this->labelParam = $this->addParam(self::PARAM_LABEL, 'Label')->label();
        $this->md5Param = $this->addParam(self::PARAM_MD5, 'MD5')->md5();
        $this->titleParam = $this->addParam(self::PARAM_TITLE, 'Title')->nameOrTitle();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(self::PARAM_ALIAS, $this->aliasParam->getAlias());
        $response->setKey(self::PARAM_ALPHA, $this->alphaParam->getAlphabetical());
        $response->setKey(self::PARAM_ALNUM, $this->alnumParam->getAlphanumeric());
        $response->setKey(self::PARAM_DATE, $this->dateParam->getDate()?->format('Y-m-d H:i:s'));
        $response->setKey(self::PARAM_EMAIL, $this->emailParam->getEmail());
        $response->setKey(self::PARAM_LABEL, $this->labelParam->getLabelValue());
        $response->setKey(self::PARAM_MD5, $this->md5Param->getMD5());
        $response->setKey(self::PARAM_TITLE, $this->titleParam->getNameOrTitle());
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    public function getGroup(): APIGroupInterface
    {
        return new TestDriverAPIGroup();
    }
}
