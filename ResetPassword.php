<?php

namespace ResetPassword;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\Message;
use Thelia\Model\LangQuery;
use Thelia\Model\MessageQuery;
use Thelia\Module\BaseModule;

class ResetPassword extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'resetpassword';

    const TOKEN_LENGTH_CONFIG_KEY = "token_length";
    const TOKEN_TIME_TO_LIVE_KEY = "token_time_to_live";

    const RESET_PASSWORD_MESSAGE_NAME = "reset_password_message";
    const RESET_ALL_PASSWORD_MESSAGE_NAME = "reset_all_password_message";

    public function postActivation(ConnectionInterface $con = null): void
    {
        if (!$this->getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, array(__DIR__ . '/Config/TheliaMain.sql'));

            $this->setConfigValue('is_initialized', true);
        }

        $this->generateEmailMessage();
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in(__DIR__ . DS . 'Config' . DS . 'update');

        $database = new Database($con);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }

        $this->generateEmailMessage();
    }

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }

    public static function getTokenLength()
    {
        return self::getConfigValue(self::TOKEN_LENGTH_CONFIG_KEY, 32);
    }

    public static function getTokenTimeToLive()
    {
        return self::getConfigValue(self::TOKEN_TIME_TO_LIVE_KEY, 86400);
    }

    protected function generateEmailMessage(): void
    {
        if (null === MessageQuery::create()->findOneByName(self::RESET_PASSWORD_MESSAGE_NAME)) {
            $message = new Message();
            $message
                ->setName(self::RESET_PASSWORD_MESSAGE_NAME)
                ->setHtmlTemplateFileName(self::RESET_PASSWORD_MESSAGE_NAME . '.html')
                ->setHtmlLayoutFileName('')
                ->setTextTemplateFileName(self::RESET_PASSWORD_MESSAGE_NAME . '.txt')
                ->setTextLayoutFileName('')
                ->setSecured(0);

            $languages = LangQuery::create()->find();

            foreach ($languages as $language) {
                $locale = $language->getLocale();

                $message->setLocale($locale);

                $message->setSubject(
                    Translator::getInstance()->trans('Your password reset link', [], ResetPassword::DOMAIN_NAME, $locale)
                );
                $message->setTitle(
                    Translator::getInstance()->trans('Password reset link', [],ResetPassword::DOMAIN_NAME, $locale)
                );
            }

            $message->save();
        }

        if (null === MessageQuery::create()->findOneByName(self::RESET_ALL_PASSWORD_MESSAGE_NAME)) {
            $message = new Message();
            $message
                ->setName(self::RESET_ALL_PASSWORD_MESSAGE_NAME)
                ->setHtmlTemplateFileName(self::RESET_ALL_PASSWORD_MESSAGE_NAME . '.html')
                ->setHtmlLayoutFileName('')
                ->setSecured(0);

            $languages = LangQuery::create()->find();

            foreach ($languages as $language) {
                $locale = $language->getLocale();

                $message->setLocale($locale);

                $message->setSubject(
                    Translator::getInstance()->trans('Reset you password', [], ResetPassword::DOMAIN_NAME, $locale)
                );
                $message->setTitle(
                    Translator::getInstance()->trans('Mail for resetting all passwords', [],ResetPassword::DOMAIN_NAME, $locale)
                );
            }

            $message->save();
        }
    }
}
