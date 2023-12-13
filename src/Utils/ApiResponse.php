<?php

namespace App\Utils;

use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * This class holds the ApiResponse when.
 *
 * @author ibilbao
 */
class ApiResponse
{
    #[Groups('show')]
    private $status = null;

    #[Groups('show')]
    private $message = null;

    #[Groups('show')]
    #[MaxDepth(2)]
    private $data = null;

    public function __construct($status, $message, $data)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
