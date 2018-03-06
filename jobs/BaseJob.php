<?php

/**
 * Отложенные задачи
 */

abstract class BaseJob
{
    abstract public function handle($params);
}