<?php


namespace ResetPassword\EventListener;

use ResetPassword\Service\ResetPasswordService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\LostPasswordEvent;
use Thelia\Core\Event\TheliaEvents;

class LostPasswordListener extends BaseAction implements EventSubscriberInterface
{
    protected $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
    }

    /**
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function sendResetLink(LostPasswordEvent $event): void
    {
        $this->resetPasswordService->sendResetLinkByEmail($event->getEmail());
        $event->stopPropagation();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::LOST_PASSWORD => ['sendResetLink', 256],
        ];
    }
}
