<?php

namespace ResetPassword\Command;

use ResetPassword\Model\CustomerForbiddenPassword;
use ResetPassword\Model\CustomerForbiddenPasswordQuery;
use ResetPassword\ResetPassword;
use ResetPassword\Service\ResetPasswordService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Model\CustomerQuery;

class ResetAllPasswordCommand extends ContainerAwareCommand
{
    protected $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        parent::__construct();
        $this->resetPasswordService = $resetPasswordService;
    }

    protected function configure()
    {
        $this
            ->setName("resetpassword:reset:all:password")
            ->setDescription("Reset all password and send reset link")
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Safeguard, if not set only first 5 customer will be reset'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initRequest();

        $customerQuery = CustomerQuery::create();

        if (true !== $input->getOption('all')) {
            $customerQuery->limit(5);
        }

        $customers = $customerQuery->find();

        foreach ($customers as $customer) {
            try {
                if (!$customer->getPassword()) {
                    continue;
                }
                $customerForbiddenPassword = CustomerForbiddenPasswordQuery::create()
                    ->filterByCustomerId($customer->getId())
                    ->filterByPassword($customer->getPassword())
                    ->findOne();

                if (null === $customerForbiddenPassword) {
                    (new CustomerForbiddenPassword())
                        ->setCustomerId($customer->getId())
                        ->setPassword($customer->getPassword())
                        ->save();
                }

                $this->resetPasswordService->sendResetLinkByEmail(
                    $customer->getEmail(),
                    ResetPassword::RESET_ALL_PASSWORD_MESSAGE_NAME,
                    ['customerId' => $customer->getId()],
                    -1
                );

                $customer->erasePassword()
                    ->save();

                $output->writeln("<info>Password reset link for customer ".$customer->getId()." sent successfully</info>");
            } catch (\Exception $exception) {
                $output->writeln("<error>".$exception->getMessage()."</error>");
            }

        }

        return 0;
    }
}
