<?php

namespace ResetPassword\Form\Front;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;
use Symfony\Component\Validator\Constraints;

class ResetPasswordForm extends BaseForm
{
    protected function buildForm():void
    {
        $form = $this->formBuilder;
        $form
            ->add(
                'email',
                HiddenType::class
            )
            ->add(
                'token',
                HiddenType::class
            )
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['min' => ConfigQuery::read('password.length', 4)]),
                ],
                'label' => Translator::getInstance()->trans('New Password'),
                'label_attr' => [
                    'for' => 'password',
                ],
            ])
            ->add('password_confirm', PasswordType::class, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length(['min' => ConfigQuery::read('password.length', 4)]),
                    new Constraints\Callback([$this, 'verifyPasswordField']),
                ],
                'label' => Translator::getInstance()->trans('Password confirmation'),
                'label_attr' => [
                    'for' => 'password_confirmation',
                ],
            ]);
        ;
    }

    public function verifyPasswordField($value, ExecutionContextInterface $context): void
    {
        $data = $context->getRoot()->getData();

        if ($data['password'] != $data['password_confirm']) {
            $context->addViolation(Translator::getInstance()->trans('password confirmation is not the same as password field'));
        }
    }
}
