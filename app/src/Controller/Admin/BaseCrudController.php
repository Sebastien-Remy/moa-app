<?php

namespace App\Controller\Admin;

use App\Exception\BusinessRuleException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class BaseCrudController extends AbstractCrudController
{
    protected function executeBusinessAction(
        callable $callback,
    ): void {
        try {
            $callback();
        } catch (BusinessRuleException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage(),
            );
        }
    }
}
