<?php
class TimeCheckDecorator extends SpamDetector {
    private $timeThreshold;

    public function __construct(SpamDetectorInterface $component, $timeThreshold = 5) {
        parent::__construct($component);
        $this->timeThreshold = $timeThreshold;
    }

    public function isSpam($data): bool {
        if ($data['form_time'] < $this->timeThreshold) {
            $this->logSpamAttempt($data);
            return true;
        }
        return $this->component->isSpam($data);
    }

    private function logSpamAttempt($data) {
        $logMessage = sprintf(
            "[%s] Spam detected: Submission too fast. File: %s, Threshold: %s seconds, Time: %s seconds\n",
            date('Y-m-d H:i:s'),
            __FILE__,
            $this->timeThreshold,
            $data['form_time']
        );
        file_put_contents('spam_log.txt', $logMessage, FILE_APPEND);
    }
}
?>
