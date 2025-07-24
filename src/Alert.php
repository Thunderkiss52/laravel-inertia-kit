<?php
namespace Thunderkiss52\LaravelInertiaKit;

class Alert
{
    public function __construct(
        public string $type,
        public ?string $name,
        public ?string $text = null,
        public bool $show = true,
        public ?array $actions = null,
    ) {

    }

    public function __serialize()
    {
        return $this->show ? [
            'type' => $this->type,
            'name' => $this->name,
            'text' => $this->text,
            'actions' => $this->actions,
        ] : [];
    }
}