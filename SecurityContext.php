<?php

namespace Ekyna\Behat;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class SecurityContext
 * @package Ekyna\Behat
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
        $minkSession = $this->getSession();

        $driver = $minkSession->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('The driver %s is not supported.', $driver);
        }

        $client    = $driver->getClient();
        $container = $this->getContainer();
        $session = $container->get('session');

        //$client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $container
            ->get('ekyna_user.user.repository')
            ->findOneBy(['email' => 'admin@example.org']);
        if (null === $user) {
            throw new \RuntimeException("Administrator user not found.");
        }

        $firewall = $container->getParameter('fos_user.firewall_name');

        $loginManager = $container->get('fos_user.security.login_manager');
        $loginManager->logInUser(
            $firewall,
            $user,
            $client->getResponse()
        );

        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        if (!$this->isMinkSessionStarted()) {
            $minkSession->visit(rtrim($this->getMinkParameter('base_url'), '/') . '/');
        }

        $minkSession->setCookie($session->getName(), $session->getId());
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    private function isMinkSessionStarted()
    {
        $minkSession = $this->getSession();

        try {
            return false !== strpos($minkSession->getCurrentUrl(), $this->getMinkParameter('base_url'));
        } catch(DriverException $e) {
            return false;
        }
    }
}
