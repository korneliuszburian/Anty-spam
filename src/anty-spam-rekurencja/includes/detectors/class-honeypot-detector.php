<?php
class HoneypotDecorator extends SpamDetector {
  public function isSpam($data): bool {
      if (!empty($data['honeypot'])) {
          return true;
      }
      return $this->component->isSpam($data);
  }
}
?>