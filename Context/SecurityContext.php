<?php

namespace Ekyna\Behat\Context;

use Behat\Mink\Exception\DriverException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Bundle\AdminBundle\Service\Security\LoginManager;

/**
 * Class SecurityContext
 * @package Ekyna\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityContext extends RawMinkContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given I am logged in as an administrator
     */
    public function iAmLoggedInAsAnAdministrator()
    {
        $container = $this->getContainer();

        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $user */
        $user = $container
            ->get('ekyna_admin.user.repository')
            ->findOneBy(['email' => 'admin@example.org']);

        if (null === $user) {
            throw new \RuntimeException("Administrator user not found.");
        }

        $firewall = 'admin';

        $token = $container->get(LoginManager::class)->logInUser($firewall, $user);

        $session = $container->get('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $minkSession = $this->getSession();
        if (!$this->isMinkSessionStarted()) {
            $minkSession->visit(rtrim($this->getMinkParameter('base_url'), '/') . '/');
        }
        $minkSession->setCookie($session->getName(), $session->getId());
    }

    private function isMinkSessionStarted()
    {
        $minkSession = $this->getSession();

        try {
            return false !== strpos($minkSession->getCurrentUrl(), $this->getMinkParameter('base_url'));
        } catch (DriverException $e) {
            return false;
        }
    }
}
