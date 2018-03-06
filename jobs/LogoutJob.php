<?php

class LogoutJob extends BaseJob
{
    public function handle($params)
    {
        session_id($params->session_id);
        session_start();
        session_destroy();
    }
}