<?php

declare(strict_types=1);
class UserMenuHTMLElement extends AbstractHTMLElement
{
    private CustomerEntity $entity;
    private FormBuilder $frm;
    private array $urls = [
        'additional_conditions' => [],
        'links' => [
            'profile' => DS . 'user_account' . DS . 'my_account_show' . DS . 'profile',
            'orders' => DS . 'user_account' . DS . 'my_account_show' . DS . 'orders',
            'address' => DS . 'user_account' . DS . 'my_account_show' . DS . 'address',
            'card' => DS . 'user_account' . DS . 'my_account_show' . DS . 'userCard',
        ],
    ];

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->entity = $params['customer']->getEntity();
        $this->frm = $params['frm'];
    }

    public function display(): array
    {
        return ['user_profile', $this->userProfile()];
    }

    private function userProfile() : string
    {
        $template = str_replace('{{userIdentification}}', $this->user(), $this->getTemplate('userMiniProfilePath'));
        $template = str_replace('{{profile_image}}', $this->entity->getProfileImage() ?: '', $template);
        $template = str_replace('{{firstName}}', $this->entity->getFirstName() ?: '', $template);
        $template = str_replace('{{lastName}}', $this->entity->getLastName() ?: '', $template);
        $template = str_replace('{{Email}}', $this->entity->getEmail() ?: '', $template);
        $template = str_replace('{{profile_link}}', $this->urls['links']['profile'], $template);
        $template = str_replace('{{orders_link}}', $this->urls['links']['orders'], $template);
        $template = str_replace('{{address_link}}', $this->urls['links']['address'], $template);
        $template = str_replace('{{user_card_link}}', $this->urls['links']['card'], $template);
        $template = str_replace('{{remove_account_frm}}', $this->removeAccountButton(), $template);
        return str_replace('{{account_route}}', '/account', $template);
    }

    private function user() : string
    {
        if ($this->entity->isInitialized('userId')) {
            return $this->frm->input([
                HiddenType::class => ['name' => 'userId'],
            ])->value($this->entity->getUserId())->noLabel()->noWrapper()->html();
        }
        return '';
    }

    private function removeAccountButton() : string
    {
        if ($this->entity->isInitialized('userId')) {
            $this->frm->form([
                'action' => '#',
                'class' => ['remove-account-frm'],
            ])->setCsrfKey('remove-account-frm' . $this->entity->getUserId());
            $buttonContent = '<span class="title"><i class="fa-solid fa-user-slash"></i></span>
                            <span>Remove account
                            </span>';
            $template = $this->getTemplate('removeAccountPath');
            $template = str_replace('{{form_begin}}', $this->frm->begin(), $template);
            $template = str_replace('{{button}}', $this->frm->input([
                ButtonType::class => ['type' => 'submit', 'class' => ['single-details-item__button"']],
            ])->content($buttonContent)->html(), $template);
            return str_replace('{{form_end}}', $this->frm->end(), $template);
        }
        return '';
    }
}