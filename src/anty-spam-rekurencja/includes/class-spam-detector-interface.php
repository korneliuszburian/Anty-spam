<?php
interface SpamDetectorInterface
{
    public function isSpam($data): bool;
}

class BaseSpamDetector implements SpamDetectorInterface {
    public function isSpam($data): bool {
        return false;
    }
}
?>