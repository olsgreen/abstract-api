<?php

namespace Olsgreen\AbstractApi\Builders;

class BuilderCollection extends AbstractBuilder
{
    protected $minimumItems;

    protected $items = [];

    protected $infoType;

    protected $label;

    public function __construct(string $label, string $infoType, array $items = [], int $minimumItems = 0)
    {
        $this->label = $label;

        $this->infoType = $infoType;

        $this->minimumItems = $minimumItems;

        foreach ($items as $item) {
            $this->add(new $infoType($item));
        }
    }

    public function add(AbstractBuilder $builder)
    {
        if (!($builder instanceof $this->infoType)) {
            throw new \Exception(
                sprintf(
                    'This collection can only accept builders of the \'%s\' type, you passed a \'%s\'.',
                    $this->infoType, get_class($builder)
                )
            );
        }

        $this->items[] = $builder;

        return $this;
    }

    public function remove(AbstractBuilder $builder)
    {
        $this->items = array_filter($this->items, function ($item) use ($builder) {
            return $builder !== $item;
        });

        return $this;
    }

    public function contains(AbstractBuilder $builder)
    {
        return in_array($builder, $this->items);
    }

    public function validate(): bool
    {
        $errors = [];

        if (!(count($this->items) >= $this->minimumItems)) {
            throw new ValidationException('There must be at least %d items in the collection.');
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function make(): array
    {
        $this->validate();

        foreach ($this->items as $item) {
            $item->validate();
        }

        return array_map(function ($item) {
            return $item->make();
        }, $this->items);
    }

    public function toArray(): array
    {
        return array_map(function($item) {
            return $item->toArray();
        }, $this->items);
    }
}