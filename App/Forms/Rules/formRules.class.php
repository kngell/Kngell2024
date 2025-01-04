<?php

declare(strict_types=1);
class FormRules
{
    public static function groups()
    {
        return [
            'name' => [
                'display' => 'Group Name',
                'required' => true,
                'max' => 65,
                'unique' => 'groups',
            ],
        ];
    }

    // Units validation
    public static function units()
    {
        return [
            'unit' => [
                'display' => 'Unit',
                'required' => true,
                'max' => 65,
                'unique' => 'units',
            ],
        ];
    }

    public static function comments()
    {
        return [
            'content' => [
                'display' => 'Comment',
                'required' => true,
                'min' => 5,
            ],
        ];
    }

    // Units validation
    public static function shippingClass()
    {
        return [
            'sh_name' => [
                'display' => 'Shipping Class',
                'required' => true,
                'max' => 150,
                'unique' => 'shipping_class',
            ],
        ];
    }

    //User datas
    public static function users(bool $table_users = true)
    {
        return [
            'terms' => [
                'required' => true,
                'display' => '',
            ],
            'first_name' => [
                'required' => true,
                'min' => 2,
                'max' => 64,
                'display' => 'Firstname',
                'Valid_string' => true,
            ],
            'last_name' => [
                'required' => true,
                'min' => 2,
                'max' => 64,
                'display' => 'Lastname',
                'Valid_string' => true,
            ],
            'user_name' => [
                'display' => 'Username',
                'required' => true,
                'unique' => $table_users ? 'users' : 'users_related_profile',
                'min' => 2,
                'max' => 20,
                // 'Valid_string' => true,
            ],
            'email' => [
                'display' => 'Email',
                'required' => true,
                'unique' => 'users',
                'max' => 150,
                'valid_email' => true,
                // 'Valid_string' => true,
                // 'Valid_domain' => true,
            ],
            'password' => [
                'display' => 'Password',
                'required' => true,
                'min' => 8,
                'max' => 64,
                'Valid_password' => true,
            ],
            'cpassword' => [
                'display' => 'Confirm Password',
                'required' => true,
                'min' => 8,
                'matches' => 'password',
                'Valid_password' => true,
            ],
        ];
    }

    //login rules
    public static function login()
    {
        return [
            'email' => [
                'display' => 'Email',
                'required' => true,
                'valid_email' => true,
            ],
            'password' => [
                'display' => 'Password',
                'required' => true,
                'min' => 5,
            ],
        ];
    }

    public static function change_pass_admin_user()
    {
        return [
            'curpass' => [
                'display' => 'Current Password',
                'required' => true,
                'max' => 65,
                'min' => 5,
            ],
            'newpass' => [
                'display' => 'New Password',
                'required' => true,
                'min' => 5,
            ],
            'cnewpass' => [
                'display' => 'Confirm New Password',
                'required' => true,
                'min' => 5,
                'matches' => 'newpass',
            ],
        ];
    }

    public static function email()
    {
        return [
            'email' => [
                'display' => 'Email',
                'min' => 4,
                'max' => 150,
                'required' => true,
                'valid_email' => true,
                'unique' => 'users',
            ],
        ];
    }

    public static function resetPass()
    {
        return [
            'password' => [
                'display' => 'Password',
                'required' => true,
                'min' => 5,
            ],
            'cpassword' => [
                'display' => 'Confirm Password',
                'required' => true,
                'min' => 5,
                'matches' => 'password',
            ],
        ];
    }

    public static function admin_login()
    {
        return [
            'username' => [
                'display' => 'Username',
                'required' => true,
                'min' => 5,
                'max' => 65,
            ],
            'password' => [
                'display' => 'Password',
                'required' => true,
                'min' => 5,
                'max' => 65,
            ],
        ];
    }

    // =======================================================================
    // Product section
    // =======================================================================
    //Add categories rules
    public static function categories()
    {
        return [
            'categorie' => [
                'display' => 'Categorie',
                'required' => true,
                'unique' => 'categories',
                'max' => 45,
            ],
        ];
    }

    public static function products()
    {
        return [
            'p_title' => [
                'display' => 'Product title',
                'required' => true,
                'min' => 2,
                'max' => 150,
            ],
            'p_short_descr' => [
                'display' => 'Product short description',
                'required' => true,
                'min' => 2,
                'max' => 255,
            ],
            'p_regular_price' => [
                'display' => 'Price',
                'is_numeric' => true,
            ],
        ];
    }

    // =======================================================================
    // Companies
    // =======================================================================
    public static function company()
    {
        return [
            'denomination' => [
                'display' => 'Company Name',
                'required' => true,
                'min' => 2,
                'max' => 150,
            ],
            'couriel' => [
                'display' => 'E-Mail',
                'required' => true,
                'min' => 2,
                'max' => 155,
                'valid_email' => true,
                'unique' => 'company',
            ],
        ];
    }

    public static function brand()
    {
        return [
            'br_name' => [
                'display' => 'Brand',
                'required' => true,
                'max' => 150,
                'unique' => 'brand',
            ],
        ];
    }

    // =======================================================================
    // Checkout
    // =======================================================================
    // Infos contact
    public static function contact_infos()
    {
        return [
            'firstName' => [
                'display' => 'First Name',
                'required' => true,
                'min' => 2,
                'max' => 64,
            ],
            'lastName' => [
                'display' => 'Last Name',
                'required' => true,
                'min' => 2,
                'max' => 64,
            ],
            'email' => [
                'display' => 'E-Mail',
                'required' => true,
                'min' => 2,
                'max' => 150,
                'valid_email' => true,
                'unique' => 'users',
            ],
        ];
    }

    // =======================================================================
    // Checkout
    // =======================================================================
    // credit_card
    public static function credit_card()
    {
        return [
            'cc_number' => [
                'display' => 'Credit Card',
                'required' => true,
                'min' => 2,
                'max' => 150,
                'unique' => 'credit_card',
            ],
            'cc_name' => [
                'display' => 'Name On Card',
                'required' => true,
                'min' => 2,
                'max' => 150,
            ],
            'cc_expiry' => [
                'display' => 'Credit Card Expiration Date',
                'required' => true,
            ],
            'cc_cvv' => [
                'display' => 'Security code',
                'required' => true,
            ],
        ];
    }

    // =======================================================================
    // Checkout
    // =======================================================================
    // address book validation
    public static function address_book()
    {
        return [
            'pays' => [
                'display' => 'Country',
                'required' => true,
                'max' => 155,
            ],
            'address1' => [
                'display' => 'Address',
                'required' => true,
            ],
            'ville' => [
                'display' => 'Town',
                'required' => true,
                'max' => 155,
            ],
            'zip_code' => [
                'display' => 'Zip Code',
                'required' => true,
                'max' => 50,
            ],
        ];
    }

    public static function general_settings()
    {
        return [
            'setting_key' => [
                'display' => 'Setting Key',
                'required' => true,
                'max' => 30,
                'unique' => 'settings',
            ],
            'setting_name' => [
                'display' => 'Setting Name',
                'required' => true,
                'max' => 30,
            ],
            'value' => [
                'display' => 'Value',
                'required' => true,
            ],
        ];
    }

    public static function sliders()
    {
        return [
            'page_slider' => [
                'display' => 'Page for slider',
                'required' => true,
                'max' => 30,
                'unique' => 'sliders',
            ],
            'slider_title' => [
                'display' => 'Sub title',
                'required' => true,
                'max' => 50,
            ],
        ];
    }
}
