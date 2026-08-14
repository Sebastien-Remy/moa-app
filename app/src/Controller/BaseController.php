<?php

namespace App\Controller;

use App\Exception\BusinessRuleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function executeBusinessAction(
        callable $callback,
    ): bool {
        try {
            $callback();

            return true;
        } catch (BusinessRuleException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage(),
            );

            return false;
        }
    }
}
