<?php

declare(strict_types=1);

class UserAddressHTMLElement extends AbstractHTMLElement
{
    private CustomerEntity $entity;
    private AddressBookPage $addressBook;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->entity = $this->params['customer']->getEntity();
        $this->addressBook = $this->params['addressBook'];
    }

    public function display(): array
    {
    }
}