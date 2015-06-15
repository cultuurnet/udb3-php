<?php

namespace CultuurNet\UDB3\Event\ReadModel;

abstract class DocumentRepositoryDecorator implements DocumentRepositoryInterface
{
    /**
     * @var DocumentRepositoryInterface
     */
    protected $decoratedRepository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->decoratedRepository = $repository;
    }

    public function get($id)
    {
        $this->decoratedRepository->get($id);
    }

    public function save(JsonDocument $readModel)
    {
        $this->decoratedRepository->save($readModel);
    }
}
