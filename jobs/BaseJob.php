<?php

abstract class BaseJob
{
    abstract public function handle($params);
}