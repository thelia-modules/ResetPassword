<?php

namespace ResetPassword\Service;

use ResetPassword\Model\PasswordResetToken;
use ResetPassword\Model\PasswordResetTokenQuery;
use ResetPassword\ResetPassword;
use Thelia\Core\Translation\Translator;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\Customer;
use Thelia\Model\CustomerQuery;
use Thelia\Tools\URL;

class ResetPasswordService
{
    protected $mailer;

    public function __construct(MailerFactory $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendResetLinkByEmail($email, $messageCode = null)
    {
        $customer = CustomerQuery::create()
            ->filterByEmail($email)
            ->findOne();

        if (null === $customer) {
            throw new \Exception(Translator::getInstance()->trans("Can't generate a token for a customer that doesn't exist.", [], ResetPassword::DOMAIN_NAME));
        }

        if (null === $messageCode) {
            $messageCode = ResetPassword::RESET_PASSWORD_MESSAGE_NAME;
        }

        $tokenLink = $this->generateResetTokenLink($customer);
        $this->mailer->sendEmailToCustomer(
            $messageCode,
            $customer,
            [
                'tokenLink' => $tokenLink,
                'tokenTimeToLive' => ResetPassword::getTokenTimeToLive()
            ]
        );
    }

    public function generateResetTokenLink(Customer $customer)
    {
        $token = bin2hex(random_bytes(ResetPassword::getTokenLength()));

        $passwordResetToken = (new PasswordResetToken())
            ->setCustomerId($customer->getId())
            ->setToken($token)
            ->setEndOfLife((new \DateTime())->add((new \DateInterval("PT".ResetPassword::getTokenTimeToLive()."S"))));

        $passwordResetToken->save();

        return URL::getInstance()->absoluteUrl("/reset_password")."?token=$token&email=".$customer->getEmail();
    }

    public function checkTokenAndUpdatePassword($email, $token, $newPassword)
    {
        $customer = CustomerQuery::create()
            ->filterByEmail($email)
            ->findOne();

        if (null === $customer) {
            throw new \Exception(Translator::getInstance()->trans("This token is invalid or doesn't match your email", [], ResetPassword::DOMAIN_NAME));
        }

        $tokenModel = $this->checkToken($token, null, $customer);

        $customer->setPassword($newPassword)
            ->save();

        $tokenModel->delete();
        return $customer;
    }

    public function checkToken($token, $email = null, Customer $customer = null): PasswordResetToken
    {
        if (null === $customer) {
            $customer = CustomerQuery::create()
                ->filterByEmail($email)
                ->findOne();
        }

        if (null === $customer) {
            throw new \Exception(Translator::getInstance()->trans("This token is invalid or doesn't match your email", [], ResetPassword::DOMAIN_NAME));
        }

        $passwordResetToken = PasswordResetTokenQuery::create()
            ->filterByCustomerId($customer->getId())
            ->filterByToken($token)
            ->findOne();

        if (null === $passwordResetToken) {
            throw new \Exception(Translator::getInstance()->trans("This token is invalid or doesn't match your email", [], ResetPassword::DOMAIN_NAME));
        }

        if ((new \DateTime()) > $passwordResetToken->getEndOfLife()) {
            throw new \Exception(Translator::getInstance()->trans("This token has expired", [], ResetPassword::DOMAIN_NAME));
        }


        return $passwordResetToken;
    }
}