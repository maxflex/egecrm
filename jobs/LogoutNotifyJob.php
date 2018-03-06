<?php

/**
 * Уведомление о логауте
 */

class LogoutNotifyJob extends BaseJob
{
    public function handle($params)
    {
        Socket::trigger('user_' . $params->user_id, 'logout_notify', []);
    }
}