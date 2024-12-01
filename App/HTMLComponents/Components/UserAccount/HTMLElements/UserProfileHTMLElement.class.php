<?php

declare(strict_types=1);

class UserProfileHTMLElement extends AbstractHTMLElement
{
    protected string $cameraSolid = 'camera' . DS . 'camera-solid.svg';
    private FormBuilder $frm;
    private CustomReflector $reflect;
    private UsersEntity $userProfile;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->frm = $params['frm'];
        $this->reflect = CustomReflector::getInstance();
        $this->userProfile = $params['userProfile'];
    }

    public function display(): array
    {
        $template = str_replace('{{user_profile_menu}}', $this->getTemplate('userProfileMenuPath'), $this->getTemplate('userProfilePath'));
        $template = str_replace('{{user_profile_data}}', $this->userProfile(), $template);
        return ['profile', str_replace('{{user_profile_formdata}}', $this->userProfileform(), $template)];
    }

    protected function userProfile() : string
    {
        $template = $this->getTemplate('profileDataPath');
        $template = str_replace('{{nom}}', $this->userProfile->getFirstName() . ' ' . $this->userProfile->getLastName(), $template);
        $template = str_replace('{{profession}}', '', $template);
        $template = str_replace('{{email}}', $this->reflect->isInitialized('email', $this->userProfile) ? $this->userProfile->getEmail() : '', $template);
        $template = str_replace('{{phone}}', $this->reflect->isInitialized('phone', $this->userProfile) ? $this->userProfile->getPhone() : '', $template);
        $template = str_replace('{{gender}}', '', $template);
        $template = str_replace('{{address}}', '', $template);
        return str_replace('{{dob}}', '', $template);
    }

    protected function userProfileform() : string
    {
        $template = $this->getTemplate('userFormDataPath');
        $frm = $this->formSettings();
        $template = str_replace('{{form_begin}}', $frm->begin(), $template);
        $template = $this->imputHidden($template, $frm);

        $template = str_replace('{{firstName}}', $frm->input([
            TextType::class => ['name' => 'firstName', 'value' => $this->userProfile->getFirstName()],
        ])->label('Prenom:')->id('account_firstName')->placeholder(' ')->req()->html(), $template);

        $template = str_replace('{{lastName}}', $this->frm->input([
            TextType::class => ['name' => 'lastName', 'value' => $this->userProfile->getLastName()],
        ])->label('Nom:')->id('account_lastName')->placeholder(' ')->req()->html(), $template);

        $template = str_replace('{{phone}}', $frm->input([
            TextType::class => ['name' => 'phone', 'value' => $this->userProfile->isInitialized('phone') ? $this->userProfile->getPhone() : ''],
        ])->label('Téléphone:')->id('account_phone')->placeholder(' ')->html(), $template);

        $template = str_replace('{{email}}', $frm->input([
            EmailType::class => ['name' => 'email', 'value' => $this->userProfile->isInitialized('email') ? $this->userProfile->getEmail() : ''],
        ])->label('Courriel:')->id('account_email')->placeholder(' ')->html(), $template);

        $template = str_replace('{{upload_profile_box}}', $this->uploadProfileBox($frm), $template);

        $template = str_replace('{{profession}}', $frm->input([
            TextType::class => ['name' => 'u_function', 'value' => ''],
        ])->label('Profession:')->id('u_function')->placeholder(' ')->html(), $template);

        $template = str_replace('{{genre}}', $frm->input([
            TextType::class => ['name' => 'gender', 'value' => ''],
        ])->label('Genre:')->id('gender')->placeholder(' ')->html(), $template);

        $template = str_replace('{{birthDay}}', $frm->input([
            DateType::class => ['name' => 'dob'],
        ])->label('Birthday:')->id('dob')->placeholder(' ')->html(), $template);

        return str_replace('{{form_end}}', $frm->end(), $template);
    }

    private function imputHidden(string $template, FormBuilder $frm)
    {
        return str_replace('{{hideInputs}}', $frm->hiddenInputs([
            'userId' => $this->userProfile->getUserId(),
            'register_date' => $this->userProfile->getCreatedAt(),
            'updated_at' => $this->userProfile->getUpdatedAt(),
            'deleted' => $this->userProfile->getDeleted(),
        ]), $template);
    }

    private function formSettings() : FormBuilder
    {
        $this->frm->form([
            'action' => '#',
            'method' => 'post',
            'class' => ['needs-validation'],
            'enctype' => 'multipart/form-data',
            'id' => 'user-profile-frm',
            'novalidate' => true,
        ])->globalClasses([
            'wrapper' => [],
            'input' => ['input-box__input'],
            'label' => ['input-box__label'],
        ]);

        return $this->frm;
    }

    private function uploadProfileBox(FormBuilder $frm) : string
    {
        $temp = $this->getTemplate('userUploadProfilePath');
        $temp = str_replace('{{camera_solid_img}}', ImageManager::asset_img($this->cameraSolid), $temp);
        $temp = str_replace('{{profileImage}}', $this->userProfile->getProfileImage(), $temp);
        return str_replace('{{profil_upload_input}}', $frm->input([
            FileType::class => ['name' => 'profileUpload'],
        ])->noLabel()->id('account_upload-profile')->html(), $temp);
    }
}