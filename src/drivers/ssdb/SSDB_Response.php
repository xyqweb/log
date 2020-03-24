<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-24
 * Time: 15:54
 */

namespace xyqWeb\log\drivers\ssdb;


class SSDB_Response
{
    public $cmd;
    public $code;
    public $data = null;
    public $message;

    function __construct($code = 'ok', $data_or_message = null)
    {
        $this->code = $code;
        if ($code == 'ok') {
            $this->data = $data_or_message;
        } else {
            $this->message = $data_or_message;
        }
    }

    function __toString()
    {
        if ($this->code == 'ok') {
            $s = $this->data === null ? '' : json_encode($this->data);
        } else {
            $s = $this->message;
        }
        return sprintf('%-13s %12s %s', $this->cmd, $this->code, $s);
    }

    function ok()
    {
        return $this->code == 'ok';
    }

    function not_found()
    {
        return $this->code == 'not_found';
    }
}