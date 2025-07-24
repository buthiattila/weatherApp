<?php

namespace core;

class JsonResponse {

    private array $response = ['success' => FALSE, 'message' => ''];

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): self {
        $this->setParam('success', $success);

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self {
        $this->setParam('message', $message);

        return $this;
    }

    /**
     * @param string $key
     * @param $data
     * @return $this
     */
    public function setParam(string $key, $data): self {
        $this->response[$key] = $data;

        return $this;
    }

    /**
     * @return void
     */
    public function send(): void {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($this->response);

        exit;
    }
}