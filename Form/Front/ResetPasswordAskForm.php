<?php

namespace ResetPassword\Form\Front;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Thelia\Form\BaseForm;

class ResetPasswordAskForm extends BaseForm
{
    protected function buildForm():void
    {
        $form = $this->formBuilder;
        $form
            ->add(
                'email',
                HiddenType::class
            );
    }
}
