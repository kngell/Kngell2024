<?php

declare(strict_types=1);

trait ControllerGettersAndSetters
{
    /**
     * @param Request $request
     * @return Controller
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param ViewInterface $view
     * @return Controller
     */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @param FormBuilder $formBuilder
     * @return Controller
     */
    public function setFormBuilder(FormBuilder $formBuilder): self
    {
        $this->formBuilder = $formBuilder;
        return $this;
    }
}