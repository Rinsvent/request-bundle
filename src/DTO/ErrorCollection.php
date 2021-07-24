<?php

namespace Rinsvent\RequestBundle\DTO;

class ErrorCollection
{
    /** @var Error[] $items */
    public array $items = [];

    public function add(Error $error): self
    {
        $this->items[] = $error;
        return $this;
    }

    public function hasErrors(): bool
    {
        return count($this->items);
    }

    public function format(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[] = $item->format();
        }
        return $result;
    }
}