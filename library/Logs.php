<?php

class Logs {

    function __construct() {

    }



    function LogRequest($fl, $log, $lv, $id = false) {
        $filext = date('Y_m_d');
		$logtofile=null;
		$todays_folder = 'systemlog/t_log/'.$fl.'/' . date('Y_m_d');
        $filename = $todays_folder.'/' . $fl . '_' . $filext . '.txt';

	    if (is_dir($todays_folder)) {
            //print_r("Is folder");die();
	    $this->PrepareLog($filename,$log, $lv, $id);
        } else {
        //  print_r("not folder");die();

           mkdir($todays_folder,0777, true);
	     $this->PrepareLog($filename,$log, $lv, $id);
        }

    }



    function PrepareLog($file_name,$log, $level, $id = false) {

        switch ($level) {
            case 1:

            file_put_contents($file_name, '[LOG START]' . "\n", FILE_APPEND);

            file_put_contents($file_name, '[' . date('Y-m-d H:i:s') . '] ' . $log . "\n", FILE_APPEND);

                break;
            case 2:
            file_put_contents($file_name, '[' . date('Y-m-d H:i:s') . '] ' . $log . "\n", FILE_APPEND);
                break;
            case 3:

            file_put_contents($file_name, '[' . date('Y-m-d H:i:s') . '] ' . $log . "\n", FILE_APPEND);
            //End Log
            file_put_contents($file_name, '[LOG STOP]' . "\n", FILE_APPEND);
                break;
            default:
                break;
        }
      //  return $logcont;
    }

}
