<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Error, ErrorableImplementation, ErrorCollection};
use Bitrix\Main\{Loader, LoaderException};

$component = new class {
    use ErrorableImplementation;
    
    private $result = [];
    
    public function __construct()
    {
        $this->errorCollection = new ErrorCollection();
    }

    /**
     * Checking installed modules
     * 
     * @return void
     */
    private function checkModules(): void
    {
        try {
            Loader::requireModule("mobile");
        } catch (LoaderException $exception) {
            $this->errorCollection[] = new Error($exception->getMessage(), $exception->getCode());
        }
    }
    
    /**
     * Returns an array with errors
     * 
     * @return array
     */
    private function showErrors(): array
    {
        return ["errors" => $this->getErrors()];
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $this->checkModules();

        if ($this->hasErrors()) {
            return $this->showErrors();
        }
        
        return [];
    }
};

return $component->execute();
