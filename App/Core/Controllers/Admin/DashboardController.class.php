<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->setLayout('admin');
    }

    public function index() : string
    {
        $this->pageTitle('Dashboard');
        return $this->render('index');
    }

    public function login() : string
    {
        $this->pageTitle('login');
        return $this->render('login');
    }

    public function editProfile() : string
    {
        $this->pageTitle('Profile');
        $profile = new UserProfileDecorator($this);
        return $this->render('edit-profile', $profile->page());
    }
}