<?php
/**
 * Cookies plugin for Craft CMS 3.x
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2017 nystudio107
 * @license   MIT License https://opensource.org/licenses/MIT
 */

namespace nystudio107\cookies\services;

use Craft;
use craft\base\Component;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

/**
 * Cookies service
 *
 * @author    nystudio107
 * @package   Cookies
 * @since     1.1.0
 */
class CookiesService extends Component
{

    /**
     * Set a cookie
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     */
    public function set(
        $name = "",
        $value = "",
        $expire = 0,
        $path = "/",
        $domain = "",
        $secure = false,
        $httpOnly = false
    ) {
        if (empty($value)) {
            Craft::$app->response->cookies->remove($name);
        } else {
            $expire = (int)$expire;
            setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
            $_COOKIE[$name] = $value;
        }
    }

    /**
     * Get a cookie
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name = "")
    {
        $result = "";
        if (isset($_COOKIE[$name])) {
            $result = $_COOKIE[$name];
        }

        return $result;
    }

    /**
     * Set a secure cookie
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     */
    public function setSecure(
        $name = "",
        $value = "",
        $expire = 0,
        $path = "/",
        $domain = "",
        $secure = false,
        $httpOnly = false
    ) {
        if (empty($value)) {
            Craft::$app->response->cookies->remove($name);
        } else {
            $expire = (int)$expire;
            $cookie = new Cookie(['name' => $name, 'value' => '']);

            try {
                $cookie->value = Craft::$app->security->hashData(base64_encode(serialize($value)));
            } catch (InvalidConfigException $e) {
                Craft::error(
                    'Error setting secure cookie: '.$e->getMessage(),
                    __METHOD__
                );

                return;
            } catch (Exception $e) {
                Craft::error(
                    'Error setting secure cookie: '.$e->getMessage(),
                    __METHOD__
                );

                return;
            }
            $cookie->expire = $expire;
            $cookie->path = $path;
            $cookie->domain = $domain;
            $cookie->secure = $secure;
            $cookie->httpOnly = $httpOnly;

            Craft::$app->response->cookies->add($cookie);
        }
    }

    /**
     * Get a secure cookie
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getSecure($name = "")
    {
        $result = "";
        $cookie = Craft::$app->request->cookies->get($name);
        if (!empty($cookie)) {
            try {
                $data = Craft::$app->security->validateData($cookie->value);
            } catch (InvalidConfigException $e) {
                Craft::error(
                    'Error getting secure cookie: '.$e->getMessage(),
                    __METHOD__
                );
                $data = false;
            } catch (Exception $e) {
                Craft::error(
                    'Error getting secure cookie: '.$e->getMessage(),
                    __METHOD__
                );
                $data = false;
            }
            if ($cookie
                && !empty($cookie->value)
                && $data !== false
            ) {
                $result = @unserialize(base64_decode($data));
            }
        }

        return $result;
    }
}
