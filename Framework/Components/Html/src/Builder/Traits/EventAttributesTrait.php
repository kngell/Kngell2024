<?php

declare(strict_types=1);

trait EventAttributesTrait
{
    protected string $onclick = '';
    protected string $ondblclick = '';
    protected string $onmousedown = '';
    protected string $onmouseup = '';
    protected string $onmouseover = '';
    protected string $onmousemove = '';
    protected string $onmouseout = '';
    protected string $onkeypress = '';
    protected string $onkeydown = '';
    protected string $onkeyup = '';
    protected string $onchange = '';

    public function onclick(string $onclick): static
    {
        $this->onclick = $onclick;
        return $this;
    }

    public function ondblclick(string $ondblclick): static
    {
        $this->ondblclick = $ondblclick;
        return $this;
    }

    public function onmousedown(string $onmousedown): static
    {
        $this->onmousedown = $onmousedown;
        return $this;
    }

    public function onmouseup(string $onmouseup): static
    {
        $this->onmouseup = $onmouseup;
        return $this;
    }

    public function onmouseover(string $onmouseover): static
    {
        $this->onmouseover = $onmouseover;
        return $this;
    }

    public function onmousemove(string $onmousemove): static
    {
        $this->onmousemove = $onmousemove;
        return $this;
    }

    public function onmouseout(string $onmouseout): static
    {
        $this->onmouseout = $onmouseout;
        return $this;
    }

    public function onkeypress(string $onkeypress): static
    {
        $this->onkeypress = $onkeypress;
        return $this;
    }

    public function onkeydown(string $onkeydown): static
    {
        $this->onkeydown = $onkeydown;
        return $this;
    }

    public function onkeyup(string $onkeyup): static
    {
        $this->onkeyup = $onkeyup;
        return $this;
    }

    public function onchange(string $onchange): static
    {
        $this->onchange = $onchange;
        return $this;
    }
}