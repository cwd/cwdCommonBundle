<?php
/*
 * This file is part of CWD Generic Panel.
 *
 * (c)2014 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class TestCase.
 *
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
abstract class TestCase extends WebTestCase
{
    protected $client = null;

    /**
     * @param string        $firewallName
     * @param UserInterface $user
     * @param array         $options
     * @param array         $server
     *
     * @return string
     */
    protected function loginUser($firewallName, UserInterface $user, array $options = array(), array $server = array())
    {
        $this->client = static::createClient();
        $session = $this->client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        //$request = new Request();
        //$event = new InteractiveLoginEvent($request, $token);
        //$this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);


        $session->set('_security_'.$firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $token;
    }
}
