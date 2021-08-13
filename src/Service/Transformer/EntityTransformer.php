<?php

namespace Rinsvent\RequestBundle\Service\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Rinsvent\Data2DTO\Transformer\Meta;
use Rinsvent\Data2DTOBundle\Service\AbstractTransformer;

class EntityTransformer extends AbstractTransformer
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {}

    /**
     * @param $data
     * @param Entity $meta
     */
    public function transform(&$data, Meta $meta): void
    {
        if (!is_string($data)) {
            return;
        }
        $repository = $this->em->getRepository($meta->class);
        $data = $repository->find((string)$data);
    }
}
