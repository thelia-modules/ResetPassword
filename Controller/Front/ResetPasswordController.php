<?php


namespace ResetPassword\Controller\Front;

use ResetPassword\Form\Front\ResetPasswordAskForm;
use ResetPassword\Form\Front\ResetPasswordForm;
use ResetPassword\Service\ResetPasswordService;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Template\ParserContext;

/**
 * @Route("/reset_password", name="reset_password")
 */
class ResetPasswordController extends BaseFrontController
{
    /**
     * @Route("", name="_view", methods="GET")
     */
    public function resetPasswordView()
    {
        return $this->render("resetPassword/reset_password");
    }

    /**
     * @Route("", name="_action", methods="POST")
     */
    public function resetPasswordAction(
        ParserContext $parserContext,
        ResetPasswordService $resetPasswordService
    )
    {
        $form = $this->createForm(ResetPasswordForm::class);

        try {
            $data = $this->validateForm($form)->getData();

            $resetPasswordService->checkTokenAndUpdatePassword($data['email'], $data['token'], $data['password']);

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $exception) {
            $form->setErrorMessage($exception->getMessage());

            $parserContext
                ->addForm($form)
                ->setGeneralError($exception->getMessage());

            return $this->generateErrorRedirect($form);
        }
    }
}
