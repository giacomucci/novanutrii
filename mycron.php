<?php
//echo 'cron started'.PHP_EOL;
ini_set('display_errors', 1);
error_reporting(E_ALL);
// run magento
require 'app/Mage.php';
Mage::app();

//Mage::getModel('points/cron')->checkAndCleanExpiredTransactions();

/*
echo date('d/m/Y', 1436013981);

require 'lib/Mobile/Detect.php';
$detect = new Mobile_Detect;
var_dump($detect->isMobile());
var_dump($detect->isTablet());
*/

//place your code below
//Mage::getModel('followupemail/crondaily')->cronJobs();
Mage::getModel('followupemail/cron')->cronJobs();
//Mage::getModel('helpdeskultimate/cron')->runJobs();
//Mage::getModel('sarp/cron')->process();
/*
Mage::getModel('productupdates/cron')->reindexAll();
Mage::getModel('productupdates/cron')->reindexPrices();
Mage::getModel('productupdates/cron')->prepareQueue();
Mage::getModel('productupdates/cron')->sendNotifications();
*/
/*
Mage::getModel('advancedreports/export_cron')->precessedExportReport();


Mage::getModel('aw_hdu3/gateway_cron')->createMailFromNewMessage();
Mage::getModel('aw_hdu3/ticket_cron')->createTicketFromMail();


Mage::getModel('awauction/cron')->queueClosedAuctions();
Mage::getModel('awauction/cron')->processQueue();
Mage::getModel('awauction/cron')->processDelayedTasks();

Mage::getModel('productupdates/cron')->reindexAll();
Mage::getModel('productupdates/cron')->reindexPrices();
Mage::getModel('productupdates/cron')->prepareQueue();
Mage::getModel('productupdates/cron')->sendNotifications();
*/

echo 'Cron finished work';

