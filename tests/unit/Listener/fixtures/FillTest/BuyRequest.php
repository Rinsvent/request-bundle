<?php

namespace Rinsvent\RequestBundle\Tests\unit\Listener\fixtures\FillTest;

use Symfony\Component\Validator\Constraints as Assert;

class BuyRequest
{
    #[Assert\NotBlank]
    public string $surname;
    public int $age;
    #[Assert\Email]
    public string $email;
}
