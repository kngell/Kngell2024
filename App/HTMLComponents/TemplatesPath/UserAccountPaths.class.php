<?php

declare(strict_types=1);

class UserAccountPaths implements TemplatePathsInterface
{
    private string $viewPath = VIEW . 'client' . DS . 'components' . DS . 'user_account' . DS . 'partials' . DS;
    private string $templatePath = APP . 'Display' . DS . 'Components' . DS . 'UserAccount' . DS . 'Templates' . DS;

    public function Paths(): CollectionInterface
    {
        return new Collection(array_merge($this->templatesPath(), $this->viewPath()));
    }

    private function templatesPath() : array
    {
        return [
            'userMiniProfilePath' => $this->templatePath . 'userMiniProfileTemplate.php',
            'userProfilePath' => $this->templatePath . 'userProfileTemplate.php',
            'buttonsPath' => $this->templatePath . 'buttonsTemplate.php',
            'removeAccountPath' => $this->templatePath . 'removeAccountFrmTemplate.php',
            'userFormPath' => $this->templatePath . 'userFormTemplate.php',
            'showOrdersPath' => $this->templatePath . 'showOrdersTemplate.php',
            'itemInfosPath' => $this->templatePath . 'ordersItemsInfosTemplate.php',
            'transactionPath' => $this->templatePath . 'userTransactionTemplate.php',
            'profileDataPath' => $this->templatePath . 'userProfileDataTemplate.php',
            'userFormDataPath' => $this->templatePath . 'userFormDataTemplate.php',
            'userProfileMenuPath' => $this->templatePath . 'userProfileMenuTemplate.php',
            'userUploadProfilePath' => $this->templatePath . 'uploadProfileBox.php',
            'userCardPath' => $this->templatePath . 'userCardTemplate.php.php',
            'userCardListPath' => $this->templatePath . 'userCardListTemplate.php',
            'userCardItemPath' => $this->templatePath . 'userCardItemTemplate.php',
            'userCardPaymentPath' => $this->templatePath . 'userPaymentCardHeadTemplate.php',
            'userCcHeadItemPath' => $this->templatePath . 'userCreditCardHeadElementTemplate.php',
        ];
    }

    private function viewPath() : array
    {
        return [
            'menuPath' => $this->viewPath . '_transaction_menu.php',
        ];
    }
}
