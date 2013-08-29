<?php

class timezoneActions extends myActions
{
  public function executeIndex()
  {
    return $this->renderText(date_default_timezone_get());
  }
}
