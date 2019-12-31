<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\IPtoCompany;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * API for plugin IPtoCompany
 *
 * @method static \Piwik\Plugins\IPtoCompany\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getCompanies($idSite, $period, $date, $segment = false)
    {
        // Get the environment paramters
        $this->getEnvParameters();

        // Send a request to the Live! plugin to get the details of the last visits
        $baseUrl    = $this->getBaseUrl();
        $request    = $this->constructRequest($idSite, $period, $date);

        // Send cURL request
        try {
            $curl       = curl_init();
            curl_setopt($curl, CURLOPT_URL, $baseUrl . $request);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response   = curl_exec($curl);
            var_dump($response);
            curl_close($curl);
        }
        catch(\Exception $e) {
            // Do something with the exception
        }

        $table = new DataTable();

        $table->addRowFromArray(array(Row::COLUMNS => array('nb_visits' => 5)));

        return $table;
    }

    /**
     * Load the environment parameters
     */
    private function getEnvParameters()
    {
        // Set the path of the env file
        $path       = __DIR__.'/.env';
        $fp         = fopen($path, 'r');

        // Parse the process and add the parameters to the environment
        while (!feof($fp))
        {
            $line = fgets($fp);

            // Process line however you like
            $line = trim($line);

            // For each line, separate the name of the param from its value
            $values = explode("=", $line);

            // Add them to the environment
            if(count($values) == 2) {
                $_ENV[$values[0]] = $values[1];
            }
        }

        // Close the process
        fclose($fp);
    }

    private function getBaseUrl()
    {
        return sprintf(
            "%s://%s/",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost:8888/Matomo'
        );
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @return DataTable
     */
    private function constructRequest($idSite, $period, $date)
    {
        $request    = "?module=API"
                    . "&method=Live.getLastVisitsDetails"
                    . "&idSite=" . $idSite
                    . "&period=" . $period
                    . "&date=" . $date
                    . "&format=JSON"
                    . "&token_auth=" . $_ENV['AUTH_TOKEN'];

        return $request;
    }
}