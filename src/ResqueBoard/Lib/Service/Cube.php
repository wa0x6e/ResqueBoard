<?php
/**
 * Cube service class
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Lib.Service
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueboard.kamisama.me
 * @since      2.0.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Lib\Service;

/**
 * Cube service class
 *
 * @subpackage ResqueBoard.Lib.Service
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @since      2.0.0
 */
class Cube extends AbstractService
{
    public static $instance = null;

    protected $settings = array();

    public function __construct($settings)
    {
        parent::bootstrap();

        $this->settings = $settings;
        parent::$serviceInstance[get_class()] = $this;
    }

    public static function init($settings)
    {
        if (self::$instance === null) {
            self::$instance = new Cube($settings);
        }

        return self::$instance;
    }

    protected function getMetric($expression)
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('The curl extension is needed to use http URLs with the CubeHandler');
        }

        $string = 'http://'.$this->settings['host'] . ':' . $this->settings['port'].'/1.0/metric?expression=' . $expression;

        $httpConnection = curl_init($string);

        if (!$httpConnection) {
            throw new DatabaseConnectionException('Unable to connect to ' . $this->settings['host'] . ':' . $this->settings['port']);
        }

        curl_setopt($httpConnection, CURLOPT_TIMEOUT, 5);
        curl_setopt($httpConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $httpConnection,
            CURLOPT_HTTPHEADER,
            array(  'Content-Type: application/json')
        );

        $response = json_decode(curl_exec($httpConnection), true);

        $responseCode = curl_getinfo($httpConnection, CURLINFO_HTTP_CODE);

        if ($responseCode === 404 || $responseCode === 0) {
            throw new DatabaseConnectionException(
                'Unable to connect to Cube Server on ' .
                $this->settings['host'] . ':' . $this->settings['port']
            );
        } else if ($responseCode !== 200) {
            throw new \Exception('Cube server return an error : ' . $response['error']);
        }

        return $response;
    }
}
