<?php

namespace Rinsvent\RequestBundle\DTO;

class Error
{
    public function __construct(
       public string $message,
       public string $path,
    ) {}

    public function format(): array
    {
        return [
            'message' => $this->message,
            'path' => $this->path,
        ];
    }
}