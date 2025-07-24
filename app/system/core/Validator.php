<?php

namespace core;

class Validator {

    private array $data = [];
    private string $errorMessage = '';

    /**
     * @param string $variableName
     * @param mixed $value
     * @param string $rules
     * @return $this
     */
    public function add(string $variableName, mixed $value, string $rules): self {
        $this->data[$variableName] = [
            'value' => $value,
            'rules' => explode('|', $rules)
        ];

        return $this;
    }

    public function validate(): bool {
        foreach ($this->data as $variableName => $data) {
            $value = $data['value'];

            foreach ($data['rules'] as $rule) {
                $ruleMethodName = $rule . 'Rule';

                if (method_exists($this, $ruleMethodName)) {
                    if (!$this->$ruleMethodName($variableName, $value)) {
                        return FALSE;
                    }
                } else {
                    $this->errorMessage = 'Ismeretlen ellenőrzési szabály (' . $rule . ')';
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }

    /**
     * @param string $variableName
     * @param mixed $value
     * @return bool
     */
    private function requiredRule(string $variableName, mixed $value): bool {
        if (!strlen($value) || $value === NULL) {
            $this->errorMessage = 'A ' . $variableName . ' mező megadása kötelező';

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $variableName
     * @param mixed $value
     * @return bool
     */
    private function textRule(string $variableName, mixed $value): bool {
        if (!empty($variableName) && !preg_match("/^[a-zA-ZáéíóöőúüűÁÉÍÓÖŐÚÜŰ\s\-\.'’]+$/u", $value)) {
            $this->errorMessage = 'A ' . $variableName . ' mező tiltott karaktereket tartalmaz';

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $variableName
     * @param mixed $value
     * @return bool
     */
    private function integerRule(string $variableName, mixed $value): bool {
        if (!empty($variableName) && preg_match('/^\d+$/', $value) !== 1) {
            $this->errorMessage = 'A ' . $variableName . ' mező csak szám lehet';

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $variableName
     * @param mixed $value
     * @return bool
     */
    private function floatRule(string $variableName, mixed $value): bool {
        if (!empty($variableName) && !is_float((float)$value)) {
            $this->errorMessage = 'A ' . $variableName . ' mező csak lebegőpontos szám lehet';

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $variableName
     * @param mixed $value
     * @return bool
     */
    private function cronExperimentRule(string $variableName, mixed $value): bool {
        if (!empty($variableName) && preg_match('/^([\*\d\/,\-]+)\s+([\*\d\/,\-]+)\s+([\*\d\/,\-]+)\s+([\*\d\/,\-]+)\s+([\*\d\/,\-]+)$/', trim($value)) !== 1) {
            $this->errorMessage = 'A ' . $variableName . ' mező csak cron ütemezési string lehet';

            return FALSE;
        }

        return TRUE;
    }


}