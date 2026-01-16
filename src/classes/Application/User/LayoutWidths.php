<?php

declare(strict_types=1);

namespace Application\User;

class LayoutWidths
{
    public const int ERROR_LAYOUT_ID_NOT_EXISTS = 133101;

    public const string WIDTH_STANDARD = 'standard';
    public const string WIDTH_MAXIMIZED = 'maximized';
    public const string DEFAULT_WIDTH = self::WIDTH_STANDARD;

    private static ?LayoutWidths $instance = null;

    /**
     * @var array<string,LayoutWidth>
     */
    private array $widths = array();

    public function __construct()
    {
        $this->registerWidth(self::WIDTH_STANDARD, t('Standard'));
        $this->registerWidth(self::WIDTH_MAXIMIZED, t('Maximized (recommended for small screens)'));
    }

    public static function getInstance() : LayoutWidths
    {
        if(!isset(self::$instance)) {
            self::$instance = new LayoutWidths();
        }

        return self::$instance;
    }

    private function registerWidth(string $id, string $label) : void
    {
        $this->widths[$id] = new LayoutWidth($id, $label);
    }

    /**
     * @return LayoutWidth[]
     */
    public function getAll() : array
    {
        return array_values($this->widths);
    }

    /**
     * @return string[]
     */
    public function getIDs() : array
    {
        return array_keys($this->widths);
    }

    public function idExists(string $id) : bool
    {
        return isset($this->widths[$id]);
    }

    public function getDefault() : LayoutWidth
    {
        return $this->getByID(self::DEFAULT_WIDTH);
    }

    public function getIDOrDefault(?string $id) : string
    {
        if($this->idExists($id)) {
            return $id;
        }

        return self::DEFAULT_WIDTH;
    }

    /**
     * @param string $id
     * @return LayoutWidth
     * @throws UserException {@see self::ERROR_LAYOUT_ID_NOT_EXISTS}
     */
    public function getByID(string $id) : LayoutWidth
    {
        if(isset($this->widths[$id])) {
            return $this->widths[$id];
        }

        throw new UserException(
            'Unknown UI layout width.',
            sprintf(
                'The layout width [%s] is not known. Available widths are [%s].',
                $id,
                implode(', ', $this->getIDs())
            ),
            self::ERROR_LAYOUT_ID_NOT_EXISTS
        );
    }
}
