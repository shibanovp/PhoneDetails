<?php

class PhoneDetails {

    /**
     * Database configuration
     */
    static private $_dsn = 'sqlite:phone_db.db';
    static private $_username = '';
    static private $_passwd = '';
    private $_db;

    public function render($strPhoneNumber, $format = 'html') {
        if (empty($strPhoneNumber))
            return $this->sendData($this->getCompletePhoneDetailes());
        $countryCode = substr($strPhoneNumber, 0, 2);
        // Determine region name by the 3rd digit
        $regionCode = substr($strPhoneNumber, 2, 1);
        $output = $geoData = array();
        switch ($countryCode) {
            case '60': $geoData['country_name'] = 'Malaysia';
                switch ($regionCode) {
                    case '4':
                    case '5': $geoData['region_name'] = 'Northern';
                        break;
                    case '2':
                    case '3':
                    case '9':
                    case '6':$geoData['region_name'] = 'Central';
                        break;
                    case '7':$geoData['region_name'] = 'Southern';
                        break;
                    default :$geoData['region_name'] = 'Others';
                }
                break;
            case '65':  // Only one region in singapore
                $geoData['country_name'] = 'Singapore';
                $geoData['region_name'] = 'Singapore';
                break;
            default : $output['result'] = 1;
                $output['message'] = 'Invalid country code';
        }
        if (!isset($output['result'])) {
            $detailes = $this->getPhoneDetailesByPhoneNumber($strPhoneNumber);
            if ($detailes) {
                $output['result'] = 0;
                $output = array_merge($output, $detailes, $geoData);
                switch ($output['status']) {
                    case 100:
                        $output['status'] = 'Active';
                        break;
                    case 101:
                        $output['status'] = 'Under Monitoring';
                        break;
                    case 102:
                        $output['status'] = 'Suspended';
                    default:
                        $output['status'] = '<Unknown>';
                        break;
                }
            } else {
                // If record not found, error
                $output['result'] = 1;
                $output['message'] = 'Phone number not found';
            }
        }
        $this->sendData($output, $format);
    }

    private function sendData($data, $format = 'html') {
        // Determine the format needed and generate
        //var_dump($data);
        switch ($format) {
            case 'html':
                $out = $table= '';
                if (count($data, COUNT_RECURSIVE)/count($data)>1){
                    foreach ($data as $row){
                        $table.='<tr>';
                        foreach ($row as $item)
                            $table.="<td>$item</td>";
                        $table.='</tr>';
                }}
                else{
                    foreach ($data as $key => $value)
                        $out.= '<div>' . $this->ucwString($key) . ": $value" . '</div>' . PHP_EOL;
                }
                $html = "
<!DOCTYPE html>
<html>
    <head>
        <link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css'>
        <meta charset='UTF-8'>
        <title>Phone Details</title>
        <style>
            .jumbotron{
                text-align:center;
            }
            .col-md-4>div{
                font-size: 1.5em;
            }
        </style>
    </head>
    <body>
        <div class='jumbotron'>
            <h1>Phone Detailes</h1>
        </div>
        <div class='container'>
            <div class='col-md-4'>&nbsp;</div>
            <div class='col-md-4'>
                " . $out . "
                <h2>New request:</h2>                  
                <form action=''>
                    <input type='text' name='phone_number' class='form-control' placeholder='Type phone number'>
                    <div class='radio'>
                        <label>
                            <input type='radio' name='format' id='optionsRadios1' value='html' checked>
                            HTML
                        </label>
                    </div>
                    <div class='radio'>
                        <label>
                            <input type='radio' name='format' id='optionsRadios2' value='json'>
                            JSON
                        </label>
                    </div>
                    <button type='submit' class='btn btn-default'>Submit</button>
                </form>
            </div>
            <div class='col-md-4'>&nbsp;</div>
            <div class='col-md-12'>
                <table class='table'>
                ".$table."
                </table>
            
            </div>
        </div>
    </body>
</html>";
                echo $html;
                break;
            case 'json':
                header("Content-Type: application/json");
                echo json_encode($data);
        }
    }

    private function getPhoneDetailesByPhoneNumber($phoneNumber) {
        $db = $this->getDb();
        $stmt = $db->prepare('SELECT `phone_number`,`status`, `telco` from `phone_details`
                WHERE `phone_number` = :phone_number');
        $stmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function ucwString($str) {
        return ucwords(str_replace('_', ' ', $str));
    }

    public function __construct() {
        $db = $this->getDb();
        $sql = "
            DROP TABLE IF EXISTS `phone_details`;
            CREATE TABLE `phone_details` (
              `phone_number` VARCHAR(20) NOT NULL,
              `status` TINYINT(1) DEFAULT NULL,
              `telco` VARCHAR(20) DEFAULT NULL,
              `customer_name` VARCHAR(200) DEFAULT NULL,
              `last_payment_date` DATE DEFAULT NULL,
              `last_payment_amount` DECIMAL(5,2) DEFAULT NULL,
              PRIMARY KEY (`phone_number`)
            );
            DELETE FROM `phone_details`;
            INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60165586780',        100,    'DIGI', 'Benjamin Law', '2013-09-18',   100.5555555765);
            INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60123691200',        101,    'MAXIS',        'Peter Tan',    '2013-09-18',   25);
            INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60198550000',        100,    'CELCOM',       'Abdullah Hukum',       '2013-09-18',   50);
            INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('6527508888',         100,    'SINGTEL',      'Richard Ooi',  '2013-09-18',   60);";
        $this->executeStatement($sql);
    }

    private function executeStatement($sql) {
        $db = $this->getDb();
        try {
            $db->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
    private function getCompletePhoneDetailes(){
        $db= $this->getDb();
        $q = "SELECT * from `phone_details`";
        $res = $db->query($q);
        $result = $res->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function getDb() {
        if (!$this->_db) {
            try {
                $this->_db = new PDO(self::$_dsn, self::$_username, self::$_passwd);
            } catch (PDOException $e) {
                echo $e->getMessage();
                die();
            }
        }
        return $this->_db;
    }

}

//Sanitize PN (digits only)
$phoneNumber = (isset($_GET['phone_number'])) ? filter_var($_GET['phone_number'], FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\d+$/"))) : null;
//Sanitize format ('html' or 'json')
$format = (isset($_GET['format'])) ? filter_var($_GET['format'], FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^(html|json)$/"))) : null;
$service = new PhoneDetails();
$service->render($phoneNumber, $format);
