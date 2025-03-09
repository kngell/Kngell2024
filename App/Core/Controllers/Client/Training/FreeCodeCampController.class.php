<?php

declare(strict_types=1);
class FreeCodeCampController extends Controller
{
    public function lesson1() : string
    {
        $this->pageTitle('Cat photos');
        $this->setLayout('freecodecamp');
        return $this->render('lesson1');
    }

    public function lesson2() : string
    {
        $this->pageTitle('Cat photos');
        $this->setLayout('freecodecamp');
        return $this->render('lesson2');
    }
}