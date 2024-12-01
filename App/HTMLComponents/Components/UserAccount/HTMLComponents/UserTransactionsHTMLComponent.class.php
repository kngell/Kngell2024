<?php

declare(strict_types=1);
class UserTransactionsHTMLComponent extends AbstractHTMLComponent
{
    private string $userOrders;
    private string $pagination;
    private string $profile;
    private string $userCard;
    private CustomReflector $reflect;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->reflect = CustomReflector::getInstance();
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($$childs as $child) {
            [$attr,$html] = $child->display();
            $this->$attr = $html;
        }
        return ['user_payment_card', $this->userTransaction()];
    }

    private function userTransaction() : string
    {
        $template = $this->getTemplate('transactionPath');
        list($content, $pagination) = $this->userTransactionItems();
        $template = str_replace('{{usercard}}', $this->userPaymentCard(), $template);
        $template = str_replace('{{transaction_content}}', $content, $template);
        $template = str_replace('{{transaction_footer}}', $pagination, $template);
        return str_replace('{{add_address_modal}}', $this->addAddressModal, $template);
    }

    private function userTransactionItems() : array
    {
        return match (true) {
            $this->reflect->isInitialized('showOrders', $this) => [
                // $this->showOrders->displayAll(),
                // $this->pagination->displayAll(),
            ],
            // $this->reflect->isInitialized('profile', $this) => [
            //     $this->profile->setUserProfile(AuthManager::currentUser()->getEntity())->displayAll(), '',
            // ],
            $this->reflect->isInitialized('showAddress', $this) => $this->showAddress(),
            $this->reflect->isInitialized('userCard', $this) => [
                $this->userCard->setCustomer($this->customerEntity)->displayAll(), '',
            ],
        };
    }
}