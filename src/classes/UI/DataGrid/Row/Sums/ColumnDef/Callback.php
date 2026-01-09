<?php

declare(strict_types=1);

use Application\Application;

class UI_DataGrid_Row_Sums_ColumnDef_Callback extends UI_DataGrid_Row_Sums_ColumnDef
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var mixed[]
     */
    private $args;

    /**
     * @param UI_DataGrid_Column $column
     * @param callable $callback
     * @param mixed[] $args
     */
    public function __construct(UI_DataGrid_Column $column, $callback, array $args=array())
    {
        parent::__construct($column);

        Application::requireCallableValid($callback);

        $this->callback = $callback;
        $this->args = $args;
    }

    public function getDefaultOptions(): array
    {
        return array();
    }

    public function resolveContent(): string
    {
        return call_user_func_array($this->callback, $this->args);
    }
}
