<?php
abstract class SpamDetector implements SpamDetectorInterface
{
    protected $component;

    public function __construct(SpamDetectorInterface $component)
    {
        $this->component = $component;
    }

    abstract public function isSpam($data): bool;
}
?>