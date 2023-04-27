<?php

class Reference
{
    private $reference = [];
    public function __construct(Template $template)
    {
        $referenceId = $template->referenceId();
        if ($referenceId) {
            $this->setReference($referenceId);
        }
    }

    public function get(): array
    {
        return $this->reference;
    }

    private function setReference($reference)
    {
        $this->reference = json_decode(
            file_get_contents(__DIR__ . '/resources/reference/' . $reference . '.json'),
            true
        );
    }
}